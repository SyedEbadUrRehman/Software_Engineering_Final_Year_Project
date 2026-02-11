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

class PostCommentUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $post;

    public function __construct(Post $post)
    {
        // Eager load sharedCircles (for channel logic) AND comments.user (for the payload)
        $this->post = $post->load(['sharedCircles', 'comments.user']);
    }

    public function broadcastOn(): array
    {
        $channels = [];

        // 1. Notify the Post Owner
        $channels[] = new PrivateChannel('App.Models.User.' . $this->post->user_id);

        // 2. Notify all Circles where this post is shared
        foreach ($this->post->sharedCircles as $circle) {
            $channels[] = new PrivateChannel('circle.' . $circle->id);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'post.comment.updated';
    }

    public function broadcastWith(): array
    {
        // Return structure matches AllPostsCollection 'comments' exactly
        return [
            'id' => $this->post->id,
            'comments' => $this->post->comments->map(function ($comment) {
                return [
                    'id'   => $comment->id,
                    'text' => $comment->text,
                    'user' => [
                        'id'   => $comment->user->id,
                        'name' => $comment->user->name,
                        'file' => $comment->user->file,
                    ],
                ];
            })->toArray()
        ];
    }
}
