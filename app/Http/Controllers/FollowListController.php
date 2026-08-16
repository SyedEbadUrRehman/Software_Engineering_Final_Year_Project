<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class FollowListController extends Controller
{
   public function followers(Request $request)
    {
        $search = $request->input('search');

        $users = Auth::user()->followers()
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('email', 'LIKE', "%{$search}%");
                });
            })
            ->get();

        return Inertia::render('Followers/Index', [
            'users' => $users,
            'filters' => ['search' => $search]
        ]);
    }

  
}
