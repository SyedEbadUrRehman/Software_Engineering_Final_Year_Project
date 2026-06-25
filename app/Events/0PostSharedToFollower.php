<?php
namespace App\Events;

use App\Http\Resources\AllPostsCollection;
use App\Models\Post;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PostSharedToFollower implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $post;
    public $followerId;

    public function __construct(Post $post, $followerId)
    {
        $this->post       = $post;
        $this->followerId = $followerId;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('App.Models.User.' . $this->followerId),
            new PrivateChannel('App.Models.User.' . $this->post->user_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'follower.post.shared';
    }

    public function broadcastWith(): array
    {
        // Format identically to API so Vue can map it directly
        return (new AllPostsCollection(collect([$this->post])))->resolve()[0];
    }
}
