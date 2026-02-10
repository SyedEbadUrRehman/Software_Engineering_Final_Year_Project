<?php

namespace App\Events;

use App\Models\Post;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class PostLikeUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $post;

    public function __construct(Post $post)
    {
        // Load relationships needed for the channels (sharedCircles) 
        // AND for the payload (likes)
        $this->post = $post->load(['sharedCircles', 'likes']);
    }

    public function broadcastOn(): array
    {
        $channels = [];

        // 1. Notify the Post Owner (Private User Channel)
        $channels[] = new PrivateChannel('App.Models.User.' . $this->post->user_id);

        // 2. Notify all Circles where this post is shared
        foreach ($this->post->sharedCircles as $circle) {
            $channels[] = new PrivateChannel('circle.' . $circle->id);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'post.like.updated';
    }

    public function broadcastWith(): array
    {
        // Send exactly what AllPostsCollection sends for 'likes'
        return [
            'id' => $this->post->id,
            'likes' => $this->post->likes->map(function ($like) {
                return [
                    'id'      => $like->id,
                    'user_id' => $like->user_id,
                    'post_id' => $like->post_id,
                ];
            })->toArray()
        ];
    }
}
