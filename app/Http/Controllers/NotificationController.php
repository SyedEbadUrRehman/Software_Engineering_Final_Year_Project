<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
   public function index()
    {
        $user = auth()->user();

        // 1. Fetch the last 30 notifications
        $allNotifications = $user->notifications()->latest()->take(30)->get();

        // 2. Partition them IN MEMORY (before marking as read)
        // 'values()' is used to reset array keys so Vue receives a clean list
        $newNotifications = $allNotifications->whereNull('read_at')->values();
        $earlierNotifications = $allNotifications->whereNotNull('read_at')->values();

        // 3. Mark unread notifications as read in the Database
        // We do this AFTER fetching so we don't lose the "New" status for the UI
        $user->unreadNotifications->markAsRead();

        return Inertia::render('Notifications', [
            'newNotifications' => $newNotifications,
            'earlierNotifications' => $earlierNotifications
        ]);
    }

    public function markAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        return redirect()->back();
    }
}
