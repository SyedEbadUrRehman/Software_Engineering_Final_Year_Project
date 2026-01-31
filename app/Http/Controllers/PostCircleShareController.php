<?php
namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use App\Models\PostCircleShare;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

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

        $postCircleShare->delete();

        return redirect()->back();
    }
}
