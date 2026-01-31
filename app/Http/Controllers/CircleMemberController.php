<?php

namespace App\Http\Controllers;

use App\Models\Circle;
use App\Models\CircleMember;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CircleMemberController extends Controller
{
    /**
     * Show all members + non-members of a specific circle
     */
    public function index(Circle $circle)
    {
        // Only circle owner can manage members
        if ($circle->user_id !== Auth::id()) {
            abort(403);
        }

        // Current members of circle
        $members = $circle->members;

        // Users who are NOT members
        $nonMembers = User::whereNotIn('id', $members->pluck('id'))
            ->where('id', '!=', Auth::id())
            ->get();

        return Inertia::render("Circles/Members", [
            "circle"     => $circle,
            "members"    => $members,
            "nonMembers" => $nonMembers,
        ]);
    }

    /**
     * Add a user into the circle
     *
     * POST /circles/{circle}/members
     */
    public function store(Request $request, Circle $circle)
    {
        // Only owner can add members
        if ($circle->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        CircleMember::firstOrCreate(
            [
                'circle_id' => $circle->id,
                'member_id' => $request->user_id,
            ],
            [
                'added_by' => Auth::id(),
            ]
        );

        return redirect()->back()->with('success', 'Member added successfully!');
    }

    /**
     * Remove a user from the circle
     *
     * DELETE /circles/{circle}/members/{user}
     */
    public function destroy(Circle $circle, User $user)
    {
        // Only owner can remove members
        if ($circle->user_id !== Auth::id()) {
            abort(403);
        }

        CircleMember::where('circle_id', $circle->id)
            ->where('member_id', $user->id)
            ->delete();

        return redirect()->back()->with('success', 'Member removed successfully!');
    }
}
