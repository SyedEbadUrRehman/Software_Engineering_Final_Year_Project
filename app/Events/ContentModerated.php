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
    public $type;      // 'post' or 'comment'
    public $status;    // 'approved', 'flagged', or 'deleted'
    public $userId;

    public function __construct($contentId, $type, $status, $userId)
    {
        $this->contentId = $contentId;
        $this->type = $type;
        $this->status = $status;
        $this->userId = $userId;
    }

    public function broadcastOn(): array
    {
        // Broadcast directly to the author's private channel
        return [
            new PrivateChannel('App.Models.User.' . $this->userId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'content.moderated';
    }
}
