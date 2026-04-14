<?php
namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContentModerated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public $contentId;
    public $type;
    public $status;
    public $userId;
    public $circleIds;
    public $postOwnerId;

    public function __construct($contentId, $type, $status, $userId, $circleIds = [], $postOwnerId = null)
    {
        $this->contentId   = $contentId;
        $this->type        = $type;
        $this->status      = $status;
        $this->userId      = $userId;
        $this->circleIds   = $circleIds; // <-- ASSIGN IT
        $this->postOwnerId = $postOwnerId ?? $userId;
    }

    public function broadcastOn(): array
    {
        // 1. Always broadcast to the author's private channel
        $channels = [
            new PrivateChannel('App.Models.User.' . $this->userId),
        ];
        if ($this->postOwnerId !== $this->userId) {
            $channels[] = new PrivateChannel('App.Models.User.' . $this->postOwnerId);
        }
        // 2. Add a channel for every circle the post is shared in
        foreach ($this->circleIds as $circleId) {
            $channels[] = new PrivateChannel('circle.' . $circleId);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'content.moderated';
    }
}
