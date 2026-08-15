<?php

namespace App\Http\Controllers;

use App\Events\PostCreated;
use App\Events\PostDeleted;
use App\Jobs\ModerateContentJob;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $post = new Post;
        $request->validate([
            // 'url'  => 'required',
            'text' => 'required',
        ]);
        // $post = (new FileService)->updateFile($post, $request, 'post');

        $post->user_id = auth()->user()->id;
        $post->text = $request->input('text');
        $post->url = $request->input('url');
        $post->status = 'pending'; // Set explicitly
        $post->save();

        // --- REAL TIME: POST CREATED ---
        // 1. Load relationships for the Resource
        $post->load(['user', 'comments.user', 'likes', 'sharedCircles', 'saves']);

        // 2. Broadcast to owner's other tabs
        broadcast(new PostCreated($post))->toOthers();
        // 3. Send to Moderation Queue
        ModerateContentJob::dispatch($post, 'post');
    }

    /**
     * Append a new URL to an existing post.
     */
    public function updateUrl(Request $request, $id)
    {
        $request->validate([
            'new_url' => 'required|string',
        ]);

        $post = Post::findOrFail($id);

        // Security check: Only the post owner can update the URL
        if ($post->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $newUrl = trim($request->input('new_url'));

        // Append with a pipe if a URL already exists, otherwise just set it
        if (! empty($post->url)) {
            $post->url = $post->url.' | '.$newUrl;
        } else {
            $post->url = $newUrl;
        }

        $post->save();
        // --- NEW: REAL-TIME BROADCAST ---
        $circleIds = $post->sharedCircles()->pluck('circles.id')->toArray();
        broadcast(new \App\Events\PostUrlUpdated($post->id, $post->url, $post->user_id, $circleIds));

        return redirect()->back()->with('success', 'URL appended successfully!');
    }
/**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $post = Post::findOrFail($id);

        if ($post->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'text' => 'required',
        ]);

        $post->text = $request->input('text');
        $post->url = $request->input('url');
        $post->save();

        // 1. Load relationships
        $post->load(['user', 'comments.user', 'likes', 'sharedCircles', 'saves']);

        // 2. Capture Shared Circle IDs
        $sharedCircleIds = $post->sharedCircles()->pluck('circles.id')->toArray();

        // 3. Broadcast to owner's other tabs and shared circles
        broadcast(new \App\Events\PostUpdated($post, $sharedCircleIds))->toOthers();

        return redirect()->back()->with('success', 'Post updated successfully!');
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
            $currentFile = public_path().$post->file;

            if (file_exists($currentFile)) {
                unlink($currentFile);
            }
        }

        $post->delete();
    }
}
