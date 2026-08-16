<?php

namespace App\Http\Controllers;

use App\Http\Resources\AllPostsCollection;
use App\Models\Post;
use App\Models\User;
use App\Services\FileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class UserController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $user = User::find($id);
        if ($user === null) {
            return redirect()->route('home.index');
        }

        $posts = Post::where('user_id', $id)->orderBy('created_at', 'desc')->get();

        $authUser = Auth::user();

        // Real counts, pulled the same way FollowController/SharePostToFollowersJob
        // already use the followers()/followings() relations elsewhere in the app —
        // no more hardcoded numbers in the frontend.
        $followersCount  = $user->followers()->count();
        $followingCount  = $user->followings()->count();

        // Whether the CURRENTLY AUTHENTICATED visitor follows this profile.
        // Always false when viewing your own profile (you can't follow yourself),
        // and false for guests (no auth user to check).
        $isFollowing = $authUser && $authUser->id !== $user->id
            ? $authUser->followings()->where('users.id', $user->id)->exists()
            : false;

        // Engagement stats — total likes/comments across ALL of this user's
        // posts, used to make the profile feel content-heavy even when the
        // post grid itself is short. Plain DB counts rather than loading
        // full like/comment relations, since we only need totals here.
        $postIds = $posts->pluck('id');
        $totalLikesReceived = $postIds->isEmpty()
            ? 0
            : \Illuminate\Support\Facades\DB::table('likes')->whereIn('post_id', $postIds)->count();
        $totalCommentsReceived = $postIds->isEmpty()
            ? 0
            : \Illuminate\Support\Facades\DB::table('comments')->whereIn('post_id', $postIds)->count();

        // Mutual followers — people the CURRENT visitor already follows who
        // also follow this profile. Classic "Followed by X, Y +N others"
        // social proof. Only computed when viewing someone else's profile
        // while logged in; empty otherwise.
        $mutualFollowers = [];
        $mutualFollowersCount = 0;

        if ($authUser && $authUser->id !== $user->id) {
            $targetFollowerIds = $user->followers()->pluck('users.id');

            $mutualQuery = $authUser->followings()->whereIn('users.id', $targetFollowerIds);

            $mutualFollowersCount = $mutualQuery->count();
            $mutualFollowers = $mutualQuery->limit(3)->get()->map(fn ($u) => [
                'id'   => $u->id,
                'name' => $u->name,
                'file' => $u->file,
            ]);
        }

        return Inertia::render('User', [
            'user' => [
                'id'                 => $user->id,
                'name'               => $user->name,
               'email'             => $user->email,
                 'file'               => $user->file,
                'bio'                => $user->bio,
                'two_factor_enabled' => (bool) $user->two_factor_enabled,
                'joined_at'          => $user->created_at->format('M Y'),
                'owner_score'        => $user->owner_score,
                'owner_score_count'   => $user->owner_score_count,
                'is_admin'            => config('app.admin_email')==$user->email,
            ],
            'postsByUser'           => new AllPostsCollection($posts),
            'followersCount'        => $followersCount,
            'followingCount'        => $followingCount,
            'isFollowing'           => $isFollowing,
            'isOwner'               => $authUser?->id === $user->id,
            'totalLikesReceived'    => $totalLikesReceived,
            'totalCommentsReceived' => $totalCommentsReceived,
            'mutualFollowers'       => $mutualFollowers,
            'mutualFollowersCount'  => $mutualFollowersCount,
        ]);
    }

    /**
     * Update the specified resource in storage.
     * (Avatar upload — unchanged from before.)
     */
    public function update(Request $request)
    {
        $request->validate(['file' => 'required|mimes:jpg,jpeg,png']);
        $user = (new FileService)->updateFile(auth()->user(), $request, 'user');
        $user->save();

        return redirect()->route('users.show', ['id' => auth()->user()->id]);
    }

    /**
     * Update the current user's display name.
     */
    public function updateName(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:50'],
        ]);

        $user = $request->user();
        $user->name = $validated['name'];
        $user->save();

        return back();
    }

    /**
     * Update the current user's bio.
     */
    public function updateBio(Request $request)
    {
        $validated = $request->validate([
            'bio' => ['nullable', 'string', 'max:500'],
        ]);

        $user = $request->user();
        $user->bio = $validated['bio'];
        $user->save();

        return back();
    }

    /**
     * Toggle the two-factor authentication switch.
     *
     * NOTE: This is UI-only for now. It persists the boolean so the
     * switch state survives a refresh, but there is no actual OTP
     * generation, verification, or recovery-code flow behind it yet.
     * Wiring up real 2FA (e.g. via a TOTP package) is a separate task.
     */
    public function toggleTwoFactor(Request $request)
    {
        $user = $request->user();
        $user->two_factor_enabled = ! $user->two_factor_enabled;
        $user->save();

        return back();
    }

    /**
     * Permanently delete the current user's account.
     *
     * Requires the user to re-type their password as a confirmation
     * step, since this is irreversible. Logs them out and destroys
     * the session before deleting the row, so no stale auth state
     * lingers after the account is gone.
     */
    public function destroyAccount(Request $request)
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $user->delete();

        return redirect()->route('login');
    }
}