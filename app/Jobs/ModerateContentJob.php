<?php
namespace App\Jobs;

use App\Events\ContentModerated;
use App\Models\ModerationLog;
use App\Models\Post;
use App\Models\User;
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
    public $tries   = 3;
    public $backoff = [10, 30, 60];

    public function __construct(Model $model, string $type)
    {
        $this->model = $model;
        $this->type  = $type;
    }

    public function handle()
    {
        // 1. Idempotent Check: Only process if it is pending
        if ($this->model->status !== 'pending') {
            return;
        }
   
        // 2. Call External API
        $response = Http::withHeaders([
            'X-API-Key'    => env('HATE_SPEECH_API_KEY'),
            'Content-Type' => 'application/json',
        ])->timeout(10)->post(env('HATE_SPEECH_API_URL'), [
            'text'    => $this->model->text,
            'user_id' => (string) $this->model->id,
        ]);

        if ($response->failed()) {
            throw new \Exception('Moderation API failed: ' . $response->status());
        }

        $apiResult = $response->json();

        // 3. Determine action (assuming API returns { "action": "allow"|"flag"|"delete" })
        $apiAction = $apiResult['action'] ?? 'allow';

        $newStatus = match ($apiAction) {
            'allow'  => 'approved',
            'flag'   => 'flagged',
            'delete' => 'deleted',
            default  => 'flagged',
        };

        // 4. Update Status
        $this->model->update(['status' => $newStatus]);
        // If  the API says it violates rules, soft delete it instantly
        if ($newStatus === 'deleted') {
            $this->model->delete();
        }
        // 5. Log it for Auditing
        ModerationLog::create([
            'moderatable_type' => get_class($this->model),
            'moderatable_id'   => $this->model->id,
            'action_taken'     => $apiAction,
            'api_response'     => json_encode($apiResult),
        ]);

        // 6. Find all circles where this content is shared
        $circleIds   = [];
        $postOwnerId = $this->model->user_id;
// We need the parent Post object for the Notification class
        $parentPost = $this->type === 'post' ? $this->model : \App\Models\Post::withTrashed()->find($this->model->post_id);

        if ($this->type === 'post') {
            $circleIds = $this->model->sharedCircles()->pluck('circles.id')->toArray();
        } elseif ($this->type === 'comment') {
            $parentPost = Post::find($this->model->post_id);
            if ($parentPost) {
                $circleIds   = $parentPost->sharedCircles()->pluck('circles.id')->toArray();
                $postOwnerId = $parentPost->user_id; // <-- GET THE POST OWNER'S ID
            }
        }

        // 7. Broadcast to UI
        broadcast(new ContentModerated(
            $this->model->id,
            $this->type,
            $newStatus,
            $this->model->user_id,
            $circleIds,
            $postOwnerId // <-- PASS IT TO THE EVENT
        ));

        // 8. Send Notifications ONLY if it wasn't deleted
        if ($newStatus !== 'deleted') {
            $this->triggerNotifications();
        }

        // 8. --- NEW: SEND NOTIFICATIONS ---
        $contentAuthor = User::find($this->model->user_id);

        if ($newStatus === 'deleted' && $parentPost && $contentAuthor) {
            // Tell the author their content was deleted
            if ($this->type === 'post') {
                $contentAuthor->notify(new \App\Notifications\PostActivityNotification(
                    $parentPost,
                    $contentAuthor, // Shows their own avatar/name
                    'moderation_post_deleted'
                ));
            } elseif ($this->type === 'comment') {
                $contentAuthor->notify(new \App\Notifications\PostActivityNotification(
                    $parentPost,
                    $contentAuthor, // Shows their own avatar/name
                    'moderation_comment_deleted'
                ));
            }
        }
    }
}
