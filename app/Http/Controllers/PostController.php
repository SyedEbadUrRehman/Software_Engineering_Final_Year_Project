<?php
namespace App\Http\Controllers;

use App\Models\Post;
use App\Events\PostCreated;
use App\Events\PostDeleted;
use Illuminate\Http\Request;
use App\Services\FileService;

class PostController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $post = new Post;
        $request->validate([
            'url' => 'required',
            'text' => 'required',
        ]);
        // $post = (new FileService)->updateFile($post, $request, 'post');

        $post->user_id = auth()->user()->id;
        $post->text    = $request->input('text');
        $post->url    = $request->input('url');
        $post->save();

        // --- REAL TIME: POST CREATED ---
        // 1. Load relationships for the Resource
        $post->load(['user', 'comments.user', 'likes', 'sharedCircles', 'saves']);

        // 2. Broadcast to owner's other tabs
        broadcast(new PostCreated($post))->toOthers();

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $post = Post::find($id);

        if (! $post) {
            return;
        }
        // Safety check

        // --- REAL TIME: POST DELETED ---
        // 1. Capture Shared Circle IDs BEFORE deleting the post
        $sharedCircleIds = $post->sharedCircles()->pluck('circles.id')->toArray();

        // 2. Broadcast the Delete Event
        broadcast(new PostDeleted($post->id, $post->user_id, $sharedCircleIds))->toOthers();

        if (! empty($post->file)) {
            $currentFile = public_path() . $post->file;

            if (file_exists($currentFile)) {
                unlink($currentFile);
            }
        }

        $post->delete();
    }
}
