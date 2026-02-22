<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReminderSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
public $postId;
    public $userId;
    public $sentAt;

    public function __construct($postId, $userId, $sentAt)
    {
        $this->postId = $postId;
        $this->userId = $userId;
        $this->sentAt = $sentAt;
    }

    public function broadcastOn(): array
    {
        // Broadcast ONLY to the user who created the reminder
        return [
            new PrivateChannel('App.Models.User.' . $this->userId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'reminder.sent';
    }

    public function broadcastWith(): array
    {
        return [
            'post_id' => $this->postId,
            'sent_at' => $this->sentAt,
        ];
    }
}
