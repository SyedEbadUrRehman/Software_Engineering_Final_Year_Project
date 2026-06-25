<?php
namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PostUnsharedFromFollowers implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $postId;
    public $followerId;
    public $authorId;

    public function __construct($postId, $followerId,$authorId)
    {
        $this->postId     = $postId;
        $this->followerId = $followerId;
        $this->authorId = $authorId;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('App.Models.User.' . $this->followerId),
            new PrivateChannel('App.Models.User.' . $this->authorId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'follower.post.unshared';
    }

    public function broadcastWith(): array
    {
        return ['id' => $this->postId];
    }
}
