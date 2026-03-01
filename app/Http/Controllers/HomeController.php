<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Inertia\Inertia;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\AllPostsCollection;

class HomeController extends Controller
{
    public function index()
    {
         $userId = Auth::id();

        // Circles where user is a member
        $circleIds = Auth::user()
            ->circleMemberships()
            ->pluck('circle_id');

        // Feed posts:
        $posts = Post::where(function ($query) use ($userId, $circleIds) {

            // 1. User's own posts
            $query->where('user_id', $userId)

                // 2. Posts shared in circles where user is member
                ->orWhereHas('sharedCircles', function ($q) use ($circleIds) {
                    $q->whereIn('circles.id', $circleIds);
                });

        })
            ->with([
                'user',
                'comments.user',
                'likes',
                'sharedCircles',
                'saves', 
                'reminders',
            ])
            ->latest()
            ->get();

        // $posts = Post::orderBy('created_at', 'desc')->get();
        return Inertia::render('Home', [
            'posts' => new AllPostsCollection($posts),
            'allUsers' => User::all(),
            'myCircleIds' => $circleIds
        ]);
    }
}
