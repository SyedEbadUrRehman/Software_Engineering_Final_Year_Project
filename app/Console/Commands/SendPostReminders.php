<?php

namespace App\Console\Commands;

use App\Jobs\SendPostReminderJob;
use App\Models\PostReminder;
use Illuminate\Console\Command;

class SendPostReminders extends Command
{
   protected $signature = 'reminders:send';
    protected $description = 'Check for due post reminders and dispatch emails';

    public function handle()
    {
        // Logic: Find reminders where 'remind_at' is in the past AND 'sent_at' is NULL
        $reminders = PostReminder::whereNull('sent_at')
                        ->where('remind_at', '<=', now())
                        ->with(['user', 'post'])
                        ->get();

        foreach ($reminders as $reminder) {
            // Dispatch to Queue
            SendPostReminderJob::dispatch($reminder);
        }

        $this->info("Dispatched {$reminders->count()} reminder emails.");
    }
}
