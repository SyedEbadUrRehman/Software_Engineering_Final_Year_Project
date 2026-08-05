<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PostUrlUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $postId;
    public $url;
    public $userId;
    public $circleIds;

    public function __construct($postId, $url, $userId, $circleIds = [])
    {
        $this->postId = $postId;
        $this->url = $url;
        $this->userId = $userId;
        $this->circleIds = $circleIds;
    }

    public function broadcastOn(): array
    {
        // 1. Broadcast to the owner's channel (for their other tabs)
        $channels = [
            new PrivateChannel('App.Models.User.' . $this->userId),
        ];

        // 2. Broadcast to any circles where the post is shared
        foreach ($this->circleIds as $circleId) {
            $channels[] = new PrivateChannel('circle.' . $circleId);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'post.url.updated';
    }
}
