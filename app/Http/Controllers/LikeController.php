<?php
namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use App\Events\PostLikeUpdated;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use App\Notifications\PostActivityNotification;
use Illuminate\Support\Facades\Notification;

class LikeController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate(['post_id' => 'required']);
        $like = new Like;

        $like->user_id = auth()->user()->id;
        $like->post_id = $request->input('post_id');
        $like->save();

        // --- REAL TIME BROADCAST ---
        $post = Post::find($request->input('post_id'));

        if ($post) {
            // Broadcast event to owner and circles
            broadcast(new PostLikeUpdated($post))->toOthers();
        }
        if ($post->user_id !== Auth::id()) {
            $postOwner = User::find($post->user_id);
            // Pass 'like' as the type
            $postOwner->notify(new PostActivityNotification($post, Auth::user(), 'like'));
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $like = Like::find($id);
        if (count(collect($like)) > 0) {
            if ($like) {
                // Fetch the post to get the updated (reduced) likes count
                $post = Post::find($like->post_id);

                // 2. Delete the like
                $like->delete();

                // 3. --- REAL TIME BROADCAST ---

                if ($post) {
                    broadcast(new PostLikeUpdated($post))->toOthers();
                }
            }

        }
    }
}
