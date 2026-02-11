<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PostDeleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $postId;
    public $userId;
    public $sharedCircleIds;

    /**
     * We pass the ID and the Circle IDs manually because 
     * the Post model might be deleted by the time the queue runs.
     */
    public function __construct($postId, $userId, $sharedCircleIds)
    {
        $this->postId = $postId;
        $this->userId = $userId;
        $this->sharedCircleIds = $sharedCircleIds;
    }

    public function broadcastOn(): array
    {
        $channels = [];

        // 1. Tell the Author to remove it
        $channels[] = new PrivateChannel('App.Models.User.' . $this->userId);

        // 2. Tell every Circle where it was shared to remove it
        foreach ($this->sharedCircleIds as $circleId) {
            $channels[] = new PrivateChannel('circle.' . $circleId);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'post.deleted';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->postId
        ];
    }
}
