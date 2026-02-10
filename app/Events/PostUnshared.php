<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PostUnshared implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

   public $postId;
    public $circleId;

    /**
     * Create a new event instance.
     */
    public function __construct($postId, $circleId)
    {
        $this->postId = $postId;
        $this->circleId = $circleId;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('circle.' . $this->circleId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'post.unshared';
    }

    /**
     * Get the data to broadcast.
     * We only need the ID to filter it out on frontend.
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->postId,
            'circle_id' => $this->circleId
        ];
    }
}
