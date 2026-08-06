<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class EnsureTwoFactorVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->two_factor_enabled) {
            if (!$request->session()->get('2fa_verified', false)) {
                
                // Allow access to the verification and logout routes to prevent redirect loops
                if (!$request->routeIs('2fa.*') && !$request->routeIs('logout')) {
                    return redirect()->route('2fa.index');
                }
            }
        }
        return $next($request);
    }
}
