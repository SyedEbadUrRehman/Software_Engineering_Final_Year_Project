<?php
namespace App\Jobs;

use App\Events\AiIdeaGenerated;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class ProcessUrlWithGrokAi implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Maximum total attempts.
     */
    public $tries = 3;

    /**
     * Retry delays:
     *
     * Attempt 1 fails → wait 10 sec
     * Attempt 2 fails → wait 20 sec
     */
    public function backoff(): array
    {
        return [10, 20];
    }

    /**
     * Don't let one request run forever.
     */
    public $timeout = 120;

    public function __construct(
        public string $url,
        public string $trackingToken,
        public int $userId
    ) {}

    /**
     * Groq rate limit:
     * maximum 3 jobs per minute.
     */
    public function middleware(): array
    {
        return [
            new RateLimited('groq-ai'),
        ];
    }

    /**
     * Called before the job starts.
     */
    protected function updateStatus(
        string $status,
        string $message
    ): void {
        Cache::put(
            "ai_idea:{$this->trackingToken}",
            [
                'status'      => $status,
                'message'     => $message,
                'description' => null,
            ],
            now()->addMinutes(15)
        );

        event(new AiIdeaGenerated(
            $this->userId,
            $this->trackingToken,
            [
                'status'      => $status,
                'message'     => $message,
                'description' => null,
            ]
        ));
    }

    public function handle(): void
    {
        /*
        |--------------------------------------------------------------------------
        | STEP 1: Fetch webpage
        |--------------------------------------------------------------------------
        */

        $this->updateStatus(
            'fetching',
            'Fetching webpage content...'
        );

        $groqApiKey = config('services.groq.key');

        $jinaResponse = Http::timeout(30)
            ->get("https://r.jina.ai/{$this->url}");

        if (! $jinaResponse->successful()) {
            throw new \RuntimeException(
                'Unable to retrieve webpage content.'
            );
        }

        $websiteContent = $jinaResponse->body();

        /*
        |--------------------------------------------------------------------------
        | STEP 2: Prepare content
        |--------------------------------------------------------------------------
        */

        $this->updateStatus(
            'preparing',
            'Preparing webpage for AI analysis...'
        );

        $cleanedContent = $this->cleanWebpageContent(
            $websiteContent
        );

        $chunkedContent = Str::limit(
            $cleanedContent,
            4000,
            "\n...[Content Truncated]..."
        );
        // \Log::debug('AI CONTENT DEBUG', [
        //     'url'             => $this->url,
        //     'original_length' => strlen($websiteContent),
        //     'cleaned_length'  => strlen($cleanedContent),
        //     'chunked_length'  => strlen($chunkedContent),
        //     'content'         => $chunkedContent,
        // ]);
        /*
        |--------------------------------------------------------------------------
        | STEP 3: AI analysis
        |--------------------------------------------------------------------------
        */

        $this->updateStatus(
            'analyzing',
            'Analyzing webpage with AI...'
        );

        $aiPrompt = <<<'PROMPT'
                You are an intelligent web-page clipping assistant for a curation platform.\n

                Your job is to analyze the provided webpage content and create a concise, useful description that explains WHY someone would want to save or clip this page.

                Do NOT merely summarize the website, homepage, or its topic. Instead, identify the page actual purpose, content, resource value, and practical use based only on the extracted webpage content.

                Determine what kind of resource it represents without being restricted to predefined categories. It could be anything, including but not limited to:
                    - a product, service, software, tool, or item available for purchase
                    - an article, blog post, report, research, or editorial
                    - documentation, API reference, technical guide, tutorial, or developer resource
                    - design resource, inspiration, UI/UX reference, guideline, or creative resource
                    - business, marketing, finance, legal, educational, or professional resource
                    - news, sports, events, announcements, or current information
                    - pricing, plans, subscriptions, trials, offers, or important dates
                    - a course, book, community, job, directory, database, or marketplace
                    - a checklist, template, framework, standard, specification, or reference
                    - any other useful webpage or resource not covered above

                    Focus on the most important and distinctive value a person would get from saving this page. Mention what the page contains and how it could be useful to a designer, developer, researcher, business professional, creator, shopper, or other relevant audience when that can be inferred from the content.

                    If the page represents something that can be purchased, explicitly recognize it as a product/service and explain what is being offered. If it contains important dates, pricing, availability, subscription information, deadlines, releases, or time-sensitive information, surface that when clearly present in the content.

                    Do not invent information that is not supported by the extracted webpage content. Ignore navigation menus, cookie notices, repetitive boilerplate, unrelated footer content, and other extraction noise whenever possible.

                    The description should normally be about 2 sentences. Use a short heading followed by a newline only when it makes the description substantially clearer. If the useful information is naturally a list, separate list items using "\n".

                    MANDATORY:
                    - Always include the actual website name, brand name, or page title naturally in the generated description.
                    - The name/title must appear in the description itself, not only in metadata.
                    - Make the description specific to the provided page, not a generic description of the website.
                    - Return ONLY a valid JSON object with exactly one key: "description".
                    - Do not return markdown code fences, explanations, analysis, or any other keys.


                    Example style for a product:
                    {"description":"Apple’s MacBook Air page presents the available laptop models, specifications, features, and purchasing options, making it useful for comparing configurations and deciding which model to buy."}

                    Example style for a time-sensitive resource:
                    {"description":"The event page for React Conf provides the conference schedule, speakers, sessions, and event details, making it a useful reference for developers planning to attend or follow the conference."}
            PROMPT;

        $response = Http::withToken($groqApiKey)
            ->timeout(60)
            ->post(
                'https://api.groq.com/openai/v1/chat/completions',
                [
                    'model'            => 'openai/gpt-oss-20b',

                    'response_format'  => [
                        'type' => 'json_object',
                    ],

                    'max_tokens'       => 600,
                    'reasoning_effort' => 'low',

                    'messages'         => [
                        [
                            'role'    => 'system',
                            'content' => $aiPrompt,
                        ],
                        [
                            'role'    => 'user',
                            'content' =>
                            "Source URL: {$this->url}\n\n" .
                            "Extracted Webpage Content:\n" .
                            $chunkedContent,
                        ],
                    ],
                ]
            );
        /*
        |--------------------------------------------------------------------------
        | STEP 4: Handle Groq response
        |--------------------------------------------------------------------------
        */

        if (! $response->successful()) {
            $error = $response->json('error.message') ?? $response->body();

            throw new \RuntimeException(
                "Groq API error: {$error}"
            );
        }

        $content = $response->json(
            'choices.0.message.content'
        );

        $decoded = json_decode($content, true);

        if (
            ! is_array($decoded) ||
            ! isset($decoded['description'])
        ) {
            throw new \RuntimeException(
                'AI returned an invalid response.'
            );
        }

        $description = trim(
            $decoded['description']
        );

        /*
        |--------------------------------------------------------------------------
        | STEP 5: Completed
        |--------------------------------------------------------------------------
        */

        $aiResponse = [
            'status'      => 'completed',
            'message'     => 'AI description generated successfully.',
            'description' => $description,
        ];

        Cache::put(
            "ai_idea:{$this->trackingToken}",
            $aiResponse,
            now()->addMinutes(15)
        );

        event(new AiIdeaGenerated(
            $this->userId,
            $this->trackingToken,
            $aiResponse
        ));
    }

    /**
     * Called when all attempts fail.
     */
    public function failed(Throwable $exception): void
    {
        $aiResponse = [
            'status'      => 'failed',
            'message'     => 'Unable to analyze this webpage.',
            'description' => null,
            'error'       => $exception->getMessage(),
        ];

        Cache::put(
            "ai_idea:{$this->trackingToken}",
            $aiResponse,
            now()->addMinutes(15)
        );

        event(new AiIdeaGenerated(
            $this->userId,
            $this->trackingToken,
            $aiResponse
        ));
    }

    private function cleanWebpageContent(string $content)
    {
        // ------------------------------------------------------------
        // 1. Remove metadata that isn't useful for AI analysis
        // ------------------------------------------------------------

        $content = preg_replace(
            '/^URL Source:.*$/mi',
            '',
            $content
        );

        $content = preg_replace(
            '/^Published Time:.*$/mi',
            '',
            $content
        );

        // ------------------------------------------------------------
        // 2. Markdown images
        //
        // ![Computer Monitor](https://image-url.jpg)
        //
        // becomes:
        //
        // Computer Monitor
        // ------------------------------------------------------------

        $content = preg_replace(
            '/!\[([^\]]*)\]\([^)]+\)/',
            '$1',
            $content
        );

        // ------------------------------------------------------------
        // 3. Markdown links
        //
        // [GitHub](https://github.com)
        //
        // becomes:
        //
        // GitHub
        // ------------------------------------------------------------

        $content = preg_replace(
            '/\[([^\]]+)\]\([^)]+\)/',
            '$1',
            $content
        );

        // ------------------------------------------------------------
        // 4. Remove ALL remaining HTTP/HTTPS URLs
        //
        // This is important for your use case.
        // ------------------------------------------------------------

        $content = preg_replace(
            '#https?://[^\s<>\])]+#iu',
            '',
            $content
        );

        // ------------------------------------------------------------
        // 5. Remove obvious extraction placeholders
        // ------------------------------------------------------------

        $content = preg_replace(
            '/\[#.*?#\]/',
            '',
            $content
        );

        // ------------------------------------------------------------
        // 6. Remove cookie-consent noise
        // ------------------------------------------------------------

        $cookiePatterns = [
            '/cookie consent/iu',
            '/cookie settings/iu',
            '/cookie preferences/iu',
            '/consent selection/iu',
            '/let[\'’]?s talk cookies/iu',
            '/necessary cookies/iu',
            '/preference cookies/iu',
            '/statistics cookies/iu',
            '/marketing cookies/iu',
            '/we use cookies/iu',
            '/this website uses cookies/iu',
            '/accept cookies/iu',
            '/manage cookies/iu',
        ];

        foreach ($cookiePatterns as $pattern) {
            $content = preg_replace($pattern, '', $content);
        }

        // ------------------------------------------------------------
        // 7. Process individual Markdown lines
        // ------------------------------------------------------------

        $lines = preg_split('/\R/', $content);

        $cleanLines = [];

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            // Remove markdown formatting characters
            $line = preg_replace('/[*_`~]+/', '', $line);

            // Remove remaining markdown link/image brackets
            $line = str_replace(
                ['[', ']'],
                '',
                $line
            );

            // Remove empty lines created by URL removal
            if (trim($line) === '') {
                continue;
            }

            $cleanLines[] = $line;
        }

        // ------------------------------------------------------------
        // 8. Remove duplicate lines
        // ------------------------------------------------------------

        $uniqueLines = [];
        $seen        = [];

        foreach ($cleanLines as $line) {
            $normalized = mb_strtolower(
                preg_replace('/\s+/', ' ', trim($line))
            );

            if ($normalized === '') {
                continue;
            }

            if (isset($seen[$normalized])) {
                continue;
            }

            $seen[$normalized] = true;
            $uniqueLines[]     = $line;
        }

        $content = implode("\n", $uniqueLines);

        // ------------------------------------------------------------
        // 9. Normalize whitespace
        // ------------------------------------------------------------

        $content = preg_replace('/[ \t]+/', ' ', $content);

        $content = preg_replace('/\n{3,}/', "\n\n", $content);

        return trim($content);
    }

}
