<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        // Fetch the admin email from your configuration (or directly from env)
        $adminEmail = config('app.admin_email');

        if (! $user || strcasecmp($user->email, $adminEmail) !== 0) {
            abort(403, 'You are not authorized to view the admin panel.');
        }

        return $next($request);
    }
}