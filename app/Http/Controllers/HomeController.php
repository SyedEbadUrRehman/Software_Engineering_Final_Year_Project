<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\AllPostsCollection;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        // Circles where user is a member
        $circleIds = Auth::user()
            ->circleMemberships()
            ->pluck('circle_id');
        // Fetch IDs of posts shared to this user by people they follow
        $followerSharedPostIds = \Illuminate\Support\Facades\DB::table('follower_post_shares')
            ->where('user_id', $userId)
            ->pluck('post_id')
            ->toArray();
        // Feed posts:
        // 1. Own posts
        $posts = Post::where('user_id', $userId)

        // 2. Shared in circles
            ->orWhereHas('sharedCircles', function ($q) use ($circleIds) {
                $q->whereIn('circles.id', $circleIds);

            })
        // 3. Shared by followers
            ->orWhereIn('id', $followerSharedPostIds)
            ->with([
                'user', 'comments.user', 'likes', 'sharedCircles', 'saves', 'reminders','authUserFollowerShare','authUserFeedback'
            ])
            ->latest()
            ->get(); // Will return unique post models

        // $posts = Post::orderBy('created_at', 'desc')->get();
        return Inertia::render('Home', [
            'posts'       => new AllPostsCollection($posts),
            'allUsers'    => User::all(),
            'myCircleIds' => $circleIds,
        ]);
    }
}