<?php

namespace App\Events;

use App\Models\Post;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PostUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

   public $post;
    public $sharedCircleIds;

    public function __construct(Post $post, array $sharedCircleIds)
    {
        $this->post = $post;
        $this->sharedCircleIds = $sharedCircleIds;
    }

    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('App.Models.User.' . $this->post->user_id),
        ];

        foreach ($this->sharedCircleIds as $circleId) {
            $channels[] = new PrivateChannel('circle.' . $circleId);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'post.updated';
    }
}
