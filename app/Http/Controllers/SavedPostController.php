<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Inertia\Inertia;
use App\Models\SavedPost;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\AllPostsCollection;

class SavedPostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
          $userId = Auth::id();

        // ✅ Get posts saved by logged-in user
        $posts = Post::whereHas('saves', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
        ->with([
            'user',
            'comments.user',
            'likes',
            'saves',
        ])
        ->latest()
        ->get();

        return Inertia::render("SavedPosts/Index", [
            "posts" => new AllPostsCollection($posts),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'post_id' => 'required|exists:posts,id',
        ]);

        SavedPost::firstOrCreate([
            'user_id' => Auth::id(),
            'post_id' => $request->post_id,
        ]);

        return redirect()->back();
    }

    /**
     * Display the specified resource.
     */
    public function show(SavedPost $savedPost)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SavedPost $savedPost)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SavedPost $savedPost)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
   public function destroy(SavedPost $savedPost)
    {
        if ($savedPost->user_id !== Auth::id()) {
            abort(403);
        }

        $savedPost->delete();

        return redirect()->back();
    }
}
