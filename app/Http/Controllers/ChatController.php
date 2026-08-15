<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use App\Notifications\PostActivityNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ChatController extends Controller
{
    public function index()
    {
        return Inertia::render('Chat/Index');
    }

    public function getContacts()
    {
        $userId = Auth::id();

        // Get users we have chatted with, along with the count of unread messages
        $contacts = User::whereHas('sentMessages', function ($q) use ($userId) {
            $q->where('receiver_id', $userId);
        })->orWhereHas('receivedMessages', function ($q) use ($userId) {
            $q->where('sender_id', $userId);
        })
        ->withCount(['sentMessages as unread_count' => function ($q) use ($userId) {
            $q->where('receiver_id', $userId)->whereNull('read_at');
        }])
        ->get();

        return response()->json($contacts);
    }

    public function search(Request $request)
    {
        $query = $request->input('q');
        $users = User::where('id', '!=', Auth::id())
            ->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('email', 'LIKE', "%{$query}%");
            })
            ->limit(10)
            ->get();

        return response()->json($users);
    }

    public function getMessages(User $user)
    {
        $userId = Auth::id();

        // Mark incoming messages from this user as read
        Message::where('sender_id', $user->id)
            ->where('receiver_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        // Fetch conversation history
        $messages = Message::where(function ($q) use ($userId, $user) {
            $q->where('sender_id', $userId)->where('receiver_id', $user->id);
        })->orWhere(function ($q) use ($userId, $user) {
            $q->where('sender_id', $user->id)->where('receiver_id', $userId);
        })->orderBy('created_at', 'asc')->get();

        return response()->json($messages);
    }

    public function sendMessage(Request $request, User $user)
    {
        $request->validate(['body' => 'required|string']);

        $authId = Auth::id();

        // Check if there are ALREADY unread messages (The Smart Notification Deduplication Rule)
        $hasUnread = Message::where('sender_id', $authId)
            ->where('receiver_id', $user->id)
            ->whereNull('read_at')
            ->exists();

        $message = Message::create([
            'sender_id' => $authId,
            'receiver_id' => $user->id,
            'body' => $request->body,
        ]);

        // If no previous unread messages exist, fire the global bell notification
        if (!$hasUnread) {
            $user->notify(new PostActivityNotification(null, Auth::user(), 'dm'));
        }

        // Always broadcast to the chat WebSocket
        broadcast(new MessageSent($message))->toOthers();

        return response()->json($message->load('sender'));
    }

    public function markAsRead(User $user)
    {
        Message::where('sender_id', $user->id)
            ->where('receiver_id', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
        
        return response()->json(['status' => 'success']);
    }
}
