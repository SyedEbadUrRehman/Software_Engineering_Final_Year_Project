<?php

namespace App\Jobs;

use App\Events\PostSharedToFollower;
use App\Models\Post;
use App\Notifications\PostActivityNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class SharePostToFollowersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

   public $post;

    public function __construct(Post $post) {
        $this->post = $post;
    }

    public function handle()
    {
        $author = $this->post->user;
        $now = now();

        $author->followers()->chunk(500, function ($followers) use ($author, $now) {
            $feedDataToInsert = [];

            foreach ($followers as $follower) {
                // Prepare Bulk Insert
                $feedDataToInsert[] = [
                    'user_id' => $follower->id,
                    'post_id' => $this->post->id,
                    'shared_by_id' => $author->id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                // Broadcast instantly
                broadcast(new PostSharedToFollower($this->post, $follower->id));

                // Notify
                $follower->notify(new PostActivityNotification($this->post, $author, 'share_follower'));
            }

            // Execute Bulk Insert
            DB::table('follower_post_shares')->insert($feedDataToInsert);
        });
    }
}
