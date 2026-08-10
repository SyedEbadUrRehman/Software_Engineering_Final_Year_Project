<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * The only account allowed into the admin panel.
     * Move this to config/services.php or .env (ADMIN_EMAIL) if you
     * ever need more than one admin.
     */
    protected const ADMIN_EMAIL = 'ebaddev@gmail.com';

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || strcasecmp($user->email, self::ADMIN_EMAIL) !== 0) {
            abort(403, 'You are not authorized to view the admin panel.');
        }

        return $next($request);
    }
}
