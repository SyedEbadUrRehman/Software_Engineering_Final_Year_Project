<?php

namespace App\Jobs;

use App\Mail\PostReminderMail;
use App\Models\PostReminder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendPostReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
public $reminder;

    public function __construct(PostReminder $reminder)
    {
        $this->reminder = $reminder;
    }

    public function handle()
    {
        // 1. Send Email
        Mail::to($this->reminder->user->email)->send(new PostReminderMail($this->reminder->post));

        // 2. Mark as Sent in DB
        $this->reminder->update(['sent_at' => now()]);
    }
}
