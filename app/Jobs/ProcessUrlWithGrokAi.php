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
        $chunkedContent = Str::limit($websiteContent, 15000, "\n...[Content Truncated]...");

        // 3. Prepare the payload using the standard Chat Completions endpoint
        // This endpoint supports the 'messages' array and JSON mode for more reliable formatting.
        $response = Http::withToken($groqApiKey)
            ->timeout(60)
            ->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => 'llama-3.1-8b-instant', // Suggested: a model with a larger context window
                'response_format' => ['type' => 'json_object'], // Forces the API to return valid JSON
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are an AI assistant that analyzes web pages. Always return your response strictly as a JSON object containing a single key: "description".'
                    ],
                    [
                        'role' => 'user',
                        'content' => "Task: Analyze the website at this URL: {$this->url}.\n\nExplain what the site is and how to use it. Include the website name in the description.\n\nWebsite Content:\n{$chunkedContent}"
                    ]
                ],
            ]);

        $aiResponse = [
            'description' => 'Could not analyze URL. Please update manually.'
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
            $error = $response->json('error.message') ?? $response->body();
            $aiResponse['description'] = "API Error: " . $error;
        }

        // 5. Cache the full result and Broadcast
        Cache::put("ai_idea:{$this->trackingToken}", $aiResponse, now()->addMinutes(15));
        
        // This triggers your Reverb/Echo listener in the Vue component
        event(new AiIdeaGenerated($this->userId, $this->trackingToken, $aiResponse));
    }
}



