<?php
namespace App\Http\Controllers;

use App\Http\Resources\AllPostsCollection;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class SearchController extends Controller
{
    //
    public function index(Request $request)
    {
        $userId = Auth::id();
        $query  = $request->q;

        // ✅ Circles where user is member (same as HomeController)
        $circleIds = Auth::user()
            ->circleMemberships()
            ->pluck('circle_id');

        // ✅ Search + Privacy Filter (Same Feed Logic)
        $posts = Post::where(function ($mainQuery) use ($userId, $circleIds) {

            // Case 1: User owns post
            $mainQuery->where('user_id', $userId)

            // Case 2: Shared in circle where user is member
                ->orWhereHas('sharedCircles', function ($q) use ($circleIds) {
                    $q->whereIn('circles.id', $circleIds);
                });

        })

        // ✅ Apply Search Filter
            ->when($query, function ($q) use ($query) {
                $q->where(function ($sub) use ($query) {

                    // Search post text
                    $sub->where('text', 'LIKE', "%{$query}%")

                    // Search user name/email
                        ->orWhereHas('user', function ($u) use ($query) {
                            $u->where('name', 'LIKE', "%{$query}%")
                                ->orWhere('email', 'LIKE', "%{$query}%");
                        });
                });
            })

        // ✅ Load Same Relations as Home Feed
            ->with([
                'user',
                'comments.user',
                'likes',
                'sharedCircles',
                'saves',
            ])

            ->latest()
            ->get();

        // ✅ Return Same Resource Format as HomeController
        return Inertia::render("Search/Index", [
            "posts"       => new AllPostsCollection($posts),
            "searchQuery" => $query,
            'myCircleIds' => $circleIds,
        ]);
    }
}
