<?php
namespace App\Http\Controllers;

use App\Events\PostLikeUpdated;
use App\Models\Like;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

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
