<?php

namespace App\Jobs;

use App\Events\PostSharedToFollower;
use App\Events\PostShareStatusUpdated;
use App\Models\Post;
use App\Notifications\PostActivityNotification;
use App\Services\OwnerScoreService;
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

    public function handle(OwnerScoreService $ownerScoreService)
    {
        $author = $this->post->user;
        $now = now();

        // Snapshot the author's reach fraction ONCE, right now, at share-time.
        // This comes from OwnerScoreService's interpolated curve, so it can
        // be any value between 40% and 100% depending on the author's score —
        // not just a fixed on/off throttle. Read once and reused for every
        // chunk below — it must NOT be re-read per-chunk, or a score change
        // mid-job could make different chunks of the same share use
        // different fractions.
        $reachFraction = $ownerScoreService->reachFractionFor($author);

        $author->followers()->chunk(500, function ($followers) use ($author, $now, $reachFraction) {
            // Throttled case: only a random $reachFraction of THIS chunk
            // receives the post (e.g. 0.5 = 50%, 0.8 = 80%, per the score
            // curve). Sampling within each chunk (rather than collecting all
            // followers first) keeps memory flat for authors with huge
            // follower counts, while still being a fair random sample of the
            // whole audience overall, since each chunk is itself an
            // arbitrary slice of the full follower set.
            // NOTE: max(1, ...) guarantees at least 1 recipient per non-empty
            // chunk, which is a deliberate "never fully zero out a chunk"
            // safeguard — for authors with only a handful of followers
            // (a single chunk), this matches the fraction exactly. For
            // authors with many thousands of followers spanning multiple
            // 500-person chunks, this floor has a negligible rounding effect
            // on the overall percentage actually reached.
            // percentage actually reached.
            if ($reachFraction < 1.0) {
                $followers = $followers->random(
                    max(1, (int) round($followers->count() * $reachFraction))
                );
            }

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

            if (empty($feedDataToInsert)) {
                return;
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