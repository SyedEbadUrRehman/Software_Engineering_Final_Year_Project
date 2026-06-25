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

class PostShareStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

     public $postId;
    public $authorId;
    public $isShared;
 
    public function __construct($postId, $authorId, $isShared)
    {
        $this->postId   = $postId;
        $this->authorId = $authorId;
        $this->isShared = $isShared;
    }
 
    public function broadcastOn(): array
    {
        // ONLY the author's channel. Never the followers.
        return [
            new PrivateChannel('App.Models.User.' . $this->authorId),
        ];
    }
 
    public function broadcastAs(): string
    {
        return 'post.share.status.updated';
    }
 
    public function broadcastWith(): array
    {
        return [
            'id'                       => $this->postId,
            'is_shared_with_followers' => $this->isShared,
        ];
    }
}
