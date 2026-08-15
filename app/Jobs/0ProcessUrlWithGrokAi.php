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
        $grokApiKey = config('services.grok.key');

        $systemPrompt = "You are an intelligent site-clipping assistant for a curation platform like insite.life. " .
            "Your task is to use your web search tool to visit and read the given URL completely. " .
            "Figure out what this webpage is about, how it functions, and what domain or context it belongs to. " .
            "Generate an insightful idea/description explaining what this page is and how it can be utilized. " .
            "MANDATORY RULE: You must natively include the specific website name or page title within your generated 'description' text string. " .
            "You must return a raw JSON object containing exactly one key: " .
            "'description' (a professional summary idea description that includes the website/page name)";

        // Standard xAI Chat Completions Endpoint
        $response = Http::withToken($grokApiKey)
            ->timeout(60)
            ->post('https://api.groq.com/openai/v1', [
                'model' => 'openai/gpt-oss-120b', // Changed to the standard stable model
                'messages' => [
                    [
                        'role' => 'system', 
                        'content' => $systemPrompt
                    ],
                    [
                        'role' => 'user', 
                        'content' => "Visit and analyze this link now: {$this->url}"
                    ]
                ],
                // Wait to enable tools until we know the basic API connects
                // 'tools' => [['type' => 'web_search']], 
                'response_format' => ['type' => 'json_object']
            ]);

        $aiResponse = [
            'description' => 'Could not analyze URL. Please update manually.'
        ];

        if ($response->successful()) {
            $content = $response->json('choices.0.message.content');
            $decoded = json_decode($content, true);
            
            if ($decoded && isset($decoded['description'])) {
                $aiResponse = [
                    'description' => trim($decoded['description'])
                ];
            } else {
                // The API worked, but it didn't return proper JSON
                $aiResponse['description'] = "API Success, but invalid JSON format returned: " . $content;
            }
        } else {
            // THE ERROR CATCHER: This will print the exact reason the API failed into your Vue text area!
            $aiResponse['description'] = "Grok API Error (" . $response->status() . "): " . $response->body();
        }

        // Cache result in Redis for 15 minutes
        Cache::put("ai_idea:{$this->trackingToken}", $aiResponse, now()->addMinutes(15));

        // Broadcast real-time update to Vue UI via Reverb
        event(new AiIdeaGenerated($this->userId, $this->trackingToken, $aiResponse));
    }
}
