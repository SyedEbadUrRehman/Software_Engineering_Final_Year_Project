<?php

namespace App\Http\Controllers;

use App\Models\PostReminder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

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
}
