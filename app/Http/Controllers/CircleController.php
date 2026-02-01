<?php
namespace App\Http\Controllers;

use App\Models\Circle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CircleController extends Controller
{

    /**
     * Show all circles of logged-in user
     */
    public function index()
    {
        $circles = Circle::where('user_id', Auth::id())
            ->latest()
            ->get();

        return Inertia::render('Circles/Index', [
            'circles' => $circles,
        ]);
    }

    /**
     * Store a new circle
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:200|unique:circles,name',
        ]);

        Circle::create([
            'user_id' => Auth::id(),
            'name'    => $request->name,
        ]);

        return redirect()->back()->with('success', 'Circle created successfully!');
    }

    /**
     * Update circle name
     */
    public function update(Request $request, Circle $circle)
    {
        if ($circle->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:200|unique:circles,name',
        ]);

        $circle->update([
            'name' => $request->name,
        ]);

        return redirect()->back()->with('success', 'Circle updated successfully!');
    }

    /**
     * Delete a circle
     */
    public function destroy(Circle $circle)
    {
        if ($circle->user_id !== Auth::id()) {
            abort(403);
        }

        $circle->delete();

        return redirect()->back()->with('success', 'Circle deleted successfully!');
    }

    public function myCircles()
    {
        $circles = Circle::where('user_id', Auth::id())
            ->latest()
            ->get();

        return response()->json($circles);
    }
}
