<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostFeedback;
use App\Services\OwnerScoreService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PostFeedbackController extends Controller
{
    //
      public function __construct(private OwnerScoreService $ownerScoreService)
    {
    }
 
    /**
     * Create or update the current user's feedback on a post.
     *
     * Deliberately a single upsert-style endpoint rather than separate
     * store/update routes — the frontend doesn't need to know whether
     * this is the user's first rating or an edit of an existing one,
     * it just submits a value and this handles both cases.
     */
    public function store(Request $request, Post $post)
    {
        // The post owner cannot rate their own post.
        if ($request->user()->id === $post->user_id) {
            abort(403);
        }
 
        $validated = $request->validate([
            'rating' => ['required', 'integer', Rule::in(PostFeedback::ALLOWED_RATINGS)],
        ]);
 
        PostFeedback::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'post_id' => $post->id,
            ],
            [
                'post_owner_id' => $post->user_id,
                'rating'        => $validated['rating'],
            ]
        );
 
        // Recalculate the post owner's score immediately. Feedback is
        // low-volume (a handful of submissions per post, not per-request
        // traffic), so doing this synchronously here — rather than
        // queuing it — keeps the score always immediately consistent
        // with what was just submitted, with no dispatch/race window.
        $this->ownerScoreService->recalculateFor($post->user);
 
        // return response()->json([
        //     'status' => 'ok',
        //     'rating' => $validated['rating'],
        // ]);
         return redirect()->back();
    }
}
