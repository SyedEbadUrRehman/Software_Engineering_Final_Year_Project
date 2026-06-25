<?php

namespace App\Jobs;

use App\Events\PostSharedToFollower;
use App\Events\PostShareStatusUpdated;
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
            }

            // BUG FIX: Insert DB records BEFORE broadcasting.
            // Previously, broadcast happened before insert, so the broadcast payload
            // (generated via AllPostsCollection) couldn't find the DB row and
            // always set is_shared_with_followers = false.
            DB::table('follower_post_shares')->insert($feedDataToInsert);

            // Now broadcast and notify AFTER the data is persisted
            foreach ($followers as $follower) {
                broadcast(new PostSharedToFollower($this->post, $follower->id));
                $follower->notify(new PostActivityNotification($this->post, $author, 'share_follower'));
            }
        });

        // Fire ONCE, only to the author's own channel, confirming the final state.
        // This is what the author's UI listens to in order to sync the button —
        // it is never sent to followers, so it can't fire N times.
        broadcast(new PostShareStatusUpdated($this->post->id, $author->id, true));
    }
}