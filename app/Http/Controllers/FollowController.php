<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Jobs\SharePostToFollowersJob;
use App\Jobs\UnsharePostFromFollowersJob;
use App\Models\Post;
use App\Models\User;
use App\Notifications\PostActivityNotification;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class FollowController extends Controller
{
    //
    // Renders the Follow UI page
    public function index()
    {
        $user = auth()->user();

        $followingIds = $user->followings()->pluck('users.id')->toArray();
        $following    = User::whereIn('id', $followingIds)->get();

        // Users not followed by the current user (excluding themselves)
        $suggestions = User::whereNotIn('id', array_merge($followingIds, [$user->id]))->get();

        return Inertia::render('Follow/Index', [
            'following'   => $following,
            'suggestions' => $suggestions,
        ]);
    }
    public function follow(User $user)
    {
        if (auth()->id() === $user->id) {
            return back();
        }

        auth()->user()->followings()->syncWithoutDetaching([$user->id]);

        // Notify them of new follower (Pass an empty Post instance)
        $user->notify(new PostActivityNotification(null, auth()->user(), 'follow'));
        // $user->notify(new PostActivityNotification(new Post(), auth()->user(), 'follow'));
        return back();
    }

    public function unfollow(User $user)
    {
        auth()->user()->followings()->detach($user->id);
        return back();
    }

    public function shareToFollowers(Post $post)
    {
        if (auth()->id() !== $post->user_id) {
            abort(403);
        }

        $alreadyShared = DB::table('follower_post_shares')
            ->where('post_id', $post->id)->where('shared_by_id', auth()->id())->exists();

        if ($alreadyShared) {
            // Idempotent: nothing to do, but still a success from the client's
            // point of view (it's already in the desired state).
            return response()->json(['status' => 'already_shared'], 200);
        }

        SharePostToFollowersJob::dispatch($post);

        // NOTE: This response fires immediately, BEFORE the queued job runs.
        // The frontend already optimistically flipped is_shared_with_followers
        // locally — this response just confirms the request was accepted.
        // The actual confirmation of final state comes later via the
        // post.share.status.updated websocket event, once the job completes.
        return response()->json(['status' => 'queued'], 202);
    }


    public function unshareFromFollowers(Post $post)
    {
        if (auth()->id() !== $post->user_id) {
            abort(403);
        }

        // Dispatch the cleanup operation to background workers
        UnsharePostFromFollowersJob::dispatch($post, auth()->id());

        return response()->json(['status' => 'queued'], 202);
    }

}