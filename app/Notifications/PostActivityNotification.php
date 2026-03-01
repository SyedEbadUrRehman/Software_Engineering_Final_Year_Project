<?php

namespace App\Notifications;

use App\Models\Post;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;

class PostActivityNotification extends Notification implements ShouldQueue
{
    use Queueable;

  public $post;
    public $user; // The person who did the action (Obaid)
    public $type; // 'share', 'like', 'comment'

    public function __construct(Post $post, User $user, string $type)
    {
        $this->post = $post;
        $this->user = $user;
        $this->type = $type;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * LOGIC: Generate message based on type
     */
    private function getMessage()
    {
        return match ($this->type) {
            'share' => 'shared a post with you',
            'like' => 'liked your post',
            'comment' => 'commented on your post',
            'reminder' => 'idea reminder that is now due in 12 hours',
            default => 'interacted with your post',
        };
    }

    public function toArray(object $notifiable): array
    {
        return [
            'post_id'       => $this->post->id,
            'notifier_id'   => $this->user->id,
            'notifier_name' => $this->user->name,
            'notifier_file' => $this->user->file,
            'type'          => $this->type,
            'message'       => $this->getMessage(), // "shared a post with you"
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'data' => $this->toArray($notifiable),
            'created_at' => now()->toIso8601String(),
            'read_at' => null,
        ]);
    }
}
