<?php
namespace App\Jobs;

use App\Events\AiIdeaGenerated;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ProcessUrlWithGrokAi implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public function __construct(
        public string $url,
        public string $trackingToken,
        public int $userId
    ) {}

    public function handle(): void
    {
        $groqApiKey = config('services.groq.key');

        // 1. Fetch website content using r.jina.ai (turns the page into clean Markdown)
        $jinaResponse = Http::timeout(30)->get("https://r.jina.ai/{$this->url}");

        $websiteContent = $jinaResponse->successful()
            ? $jinaResponse->body()
            : "Content could not be retrieved from the URL.";

        // 2. Chunking / Truncation
        // Web pages can be huge. We truncate the text to roughly 15,000 characters
        // to ensure it safely fits within standard LLM context windows.
        $chunkedContent = Str::limit($websiteContent, 1500, "\n...[Content Truncated]...");

        // 3. Prepare the payload using the standard Chat Completions endpoint
        // This endpoint supports the 'messages' array and JSON mode for more reliable formatting.
        $aiPrompt = 'You are an intelligent web-page clipping assistant for a curation platform.\n

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
                    {"description":"The event page for React Conf provides the conference schedule, speakers, sessions, and event details, making it a useful reference for developers planning to attend or follow the conference."}';

        $response = Http::withToken($groqApiKey)
            ->timeout(60)
            ->post('https://api.groq.com/openai/v1/chat/completions', [
                'model'           => 'llama-3.1-8b-instant',    // Suggested: a model with a larger context window
                'response_format' => ['type' => 'json_object'], // Forces the API to return valid JSON
                'messages'        => [
                    [
                        'role'    => 'system',
                        'content' => $aiPrompt,
                    ],
                    [
                        'role'    => 'user',
                        'content' => "Task: Analyze the following extracted webpage content and create a useful clipping description.\n\nSource URL: {$this->url}\n\nExtracted Webpage Content:\n {$chunkedContent} ",
                    ],
                ],
            ]);

        $aiResponse = [
            'description' => 'Could not analyze URL. Please update manually.',
        ];

        // 4. Process the AI Response
        if ($response->successful()) {
            // Standard path for chat/completions responses
            $content = $response->json('choices.0.message.content');

            $decoded = json_decode($content, true);

            if ($decoded && isset($decoded['description'])) {
                $aiResponse['description'] = trim($decoded['description']);
            } else {
                $aiResponse['description'] = $content ?? 'No content returned from AI.';
            }
        } else {
            $error                     = $response->json('error.message') ?? $response->body();
            $aiResponse['description'] = "API Error: " . $error;
        }

        // 5. Cache the full result and Broadcast
        Cache::put("ai_idea:{$this->trackingToken}", $aiResponse, now()->addMinutes(15));

        // This triggers your Reverb/Echo listener in the Vue component
        event(new AiIdeaGenerated($this->userId, $this->trackingToken, $aiResponse));
    }
}
