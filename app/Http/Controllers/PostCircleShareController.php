<?php
namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Circle;
use App\Events\PostShared;
use App\Events\PostUnshared;
use Illuminate\Http\Request;
use App\Models\PostCircleShare;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use App\Notifications\PostActivityNotification;

class PostCircleShareController extends Controller
{
    /**
     * Share post into a circle
     */
    public function store(Request $request, Post $post)
    {
        $request->validate([
            'circle_id' => 'required|exists:circles,id',
        ]);

        // Only post owner can share
        if ($post->user_id !== Auth::id()) {
            abort(403);
        }

        PostCircleShare::firstOrCreate([
            'post_id'   => $post->id,
            'circle_id' => $request->circle_id,
        ], [
            'shared_by' => Auth::id(),
        ]);

        // 1. Load relationships required by AllPostsCollection
        $post->load(['user', 'comments.user', 'likes', 'sharedCircles', 'saves']);
        // 2. Broadcast the event
        broadcast(new PostShared($post, $request->circle_id))->toOthers();

        // 3. SEND NOTIFICATIONS (New Logic)
        // A. Find the circle
        $circle = Circle::find($request->circle_id);

        // B. Get all members of the circle EXCEPT the person sharing it
        // Assuming you have a 'members' relationship on your Circle model
        $membersToNotify = $circle->members()
            ->where('users.id', '!=', Auth::id()) // Don't notify myself
            ->get();

        if ($membersToNotify->count() > 0) {
            // Pass 'share' as the type
            Notification::send($membersToNotify, new PostActivityNotification($post, Auth::user(), 'share'));
        }
        return redirect()->back()->with('success', 'Post shared into circle!');
    }

    /**
     * Remove share from circle
     */
    public function destroy(PostCircleShare $postCircleShare)
    {
        if ($postCircleShare->shared_by !== Auth::id()) {
            abort(403);
        }
        // 1. Capture IDs before deleting (so we can send them in the event)
        $postId   = $postCircleShare->post_id;
        $circleId = $postCircleShare->circle_id;

        // 2. Delete the record
        $postCircleShare->delete();

        // 3. Broadcast Unshare Event
        // We use queue to make it faster for the user
        broadcast(new PostUnshared($postId, $circleId))->toOthers();

        return redirect()->back();
    }
}
