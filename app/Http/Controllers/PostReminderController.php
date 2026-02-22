<?php

namespace App\Http\Controllers;

use App\Models\PostReminder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class PostReminderController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'post_id' => 'required|exists:posts,id',
            'due_at' => 'required|date|after:+1 day', // Minimum 1 day delay rule
        ]);

        $dueAt = Carbon::parse($request->due_at);
        // Calculate reminder time: 12 hours before the due date
        $remindAt = $dueAt->copy()->subHours(12);

        PostReminder::create([
            'user_id' => auth()->id(),
            'post_id' => $request->post_id,
            'due_at' => $dueAt,
            'remind_at' => $remindAt,
        ]);

        return redirect()->back()->with('success', 'Reminder set for 12 hours before due date!');
    }
    public function update(Request $request, $postId)
    {
        $request->validate([
            'due_at' => 'required|date|after:+1 day',
        ]);

        // Find the reminder belonging ONLY to the logged-in user for this specific post
        $reminder = PostReminder::where('post_id', $postId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $dueAt = Carbon::parse($request->due_at);
        $remindAt = $dueAt->copy()->subHours(12);

        $reminder->update([
            'due_at' => $dueAt,
            'remind_at' => $remindAt,
            'sent_at' => null // Reset sent status in case they update an already sent reminder
        ]);

        return redirect()->back()->with('success', 'Reminder updated!');
    }

    public function destroy($postId)
    {
        // Ensure they only delete their own reminder
        $reminder = PostReminder::where('post_id', $postId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $reminder->delete();

        return redirect()->back()->with('success', 'Reminder deleted!');
    }
}
