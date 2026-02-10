<?php

namespace App\Events;

use App\Models\Post;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use App\Http\Resources\AllPostsCollection;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class PostShared implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

   public $post;
    public $circleId;

    public function __construct(Post $post, $circleId)
    {
        $this->post = $post;
        $this->circleId = $circleId;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('circle.' . $this->circleId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'post.shared';
    }

    /**
     * Get the data to broadcast.
     * CLEANED UP: Uses your AllPostsCollection Resource
     */
    public function broadcastWith(): array
    {
        // 1. Wrap the single post in a collection
        // 2. Pass it to your Resource
        // 3. Resolve it and take the first item [0]
        return (new AllPostsCollection(collect([$this->post])))->resolve()[0];
    }
}
