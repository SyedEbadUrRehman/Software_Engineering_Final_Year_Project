<?php
namespace App\Jobs;

use App\Events\PostUnsharedFromFollowers;
use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class UnsharePostFromFollowersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $post;
    public $authorId;

    public function __construct(Post $post, $authorId)
    {
        $this->post     = $post;
        $this->authorId = $authorId;
    }

    public function handle()
    {
        // 1. Get all follower IDs who received this post before deleting them
        $followerIds = DB::table('follower_post_shares')
            ->where('post_id', $this->post->id)
            ->where('shared_by_id', $this->authorId)
            ->pluck('user_id');

        // 2. Perform a massive fast deletion using our database index
        DB::table('follower_post_shares')
            ->where('post_id', $this->post->id)
            ->where('shared_by_id', $this->authorId)
            ->delete();

        // 3. Notify followers in chunks via WebSockets to remove it from their UI
        foreach ($followerIds as $followerId) {
            broadcast(new PostUnsharedFromFollowers($this->post->id, $followerId, $this->authorId));
        }
    }
}
