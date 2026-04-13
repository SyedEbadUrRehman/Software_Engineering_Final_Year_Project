<?php

namespace App\Jobs;

use App\Events\ContentModerated;
use App\Models\ModerationLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class ModerateContentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $model;
    public $type; // 'post' or 'comment'

    // Retry & Failure Handling if API is down
    public $tries = 3;
    public $backoff = [10, 30, 60]; 

    public function __construct(Model $model, string $type)
    {
        $this->model = $model;
        $this->type = $type;
    }

    public function handle()
    {
        // 1. Idempotent Check: Only process if it is pending
        if ($this->model->status !== 'pending') {
            return; 
        }

        // 2. Call External API
        $response = Http::withHeaders([
            'X-API-Key' => env('HATE_SPEECH_API_KEY'),
            'Content-Type' => 'application/json',
        ])->timeout(10)->post(env('HATE_SPEECH_API_URL'), [
            'text' => $this->model->text,
            'user_id' => (string) $this->model->id
        ]);

        if ($response->failed()) {
            throw new \Exception('Moderation API failed: ' . $response->status());
        }

        $apiResult = $response->json();
        
        // 3. Determine action (assuming API returns { "action": "allow"|"flag"|"delete" })
        $apiAction = $apiResult['action'] ?? 'allow'; 

        $newStatus = match($apiAction) {
            'allow' => 'approved',
            'flag' => 'flagged',
            'delete' => 'deleted',
            default => 'flagged', 
        };

        // 4. Update Status
        $this->model->update(['status' => $newStatus]);

        // 5. Log it for Auditing
        ModerationLog::create([
            'moderatable_type' => get_class($this->model),
            'moderatable_id' => $this->model->id,
            'action_taken' => $apiAction,
            'api_response' => json_encode($apiResult)
        ]);

        // 6. Broadcast to UI
        broadcast(new ContentModerated(
            $this->model->id, 
            $this->type, 
            $newStatus, 
            $this->model->user_id
        ));

        // 7. Send Notifications ONLY if it wasn't deleted
        if ($newStatus !== 'deleted') {
            $this->triggerNotifications();
        }
    }

    private function triggerNotifications()
    {
        // Example: If it's a comment, notify the post owner here
        // if ($this->type === 'comment') { ... notify ... }
    }
}
