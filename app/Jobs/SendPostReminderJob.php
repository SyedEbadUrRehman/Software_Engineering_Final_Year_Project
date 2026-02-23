<?php
namespace App\Jobs;

use App\Events\ReminderSent;
use App\Mail\PostReminderMail;
use App\Models\PostReminder;
use App\Notifications\PostActivityNotification;
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

        // sleep(30);
        // 1. Send Email
        Mail::to($this->reminder->user->email)->send(new PostReminderMail($this->reminder->post));

        // 2. Mark as Sent in DB
        $now = now();
        $this->reminder->update(['sent_at' => $now]);

        // 3. --- REAL TIME UI UPDATE ---
        broadcast(new ReminderSent($this->reminder->post_id, $this->reminder->user_id, $now));
        // 4. --- NEW: SEND IN-APP REAL-TIME NOTIFICATION ---
        $this->reminder->user->notify(new PostActivityNotification(
            $this->reminder->post,
            $this->reminder->user,
            'reminder'
        ));
    }
}
