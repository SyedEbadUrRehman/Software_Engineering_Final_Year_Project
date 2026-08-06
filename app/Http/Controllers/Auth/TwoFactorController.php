<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\SendOTPCode;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class TwoFactorController extends Controller
{
    public function index(Request $request)
    {
        // If they bypass it somehow but are already verified
        if ($request->session()->get('2fa_verified') === true) {
            return redirect()->intended(RouteServiceProvider::HOME);
        }

        return Inertia::render('Auth/VerifyOTP', [
            'email' => Auth::user()->email
        ]);
    }

    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|numeric|digits:6',
        ]);

        $user = Auth::user();
        $cachedCode = Cache::get('2fa_code_' . $user->id);

        if ($cachedCode && $cachedCode == $request->code) {
            // Success! Clear cache and verify session
            Cache::forget('2fa_code_' . $user->id);
            $request->session()->put('2fa_verified', true);

            return redirect()->intended(RouteServiceProvider::HOME);
        }

        return back()->withErrors(['code' => 'The provided code is invalid or has expired.']);
    }

    public function resend(Request $request)
    {
        $user = Auth::user();
        $code = rand(100000, 999999);
        
        Cache::put('2fa_code_' . $user->id, $code, now()->addMinutes(10));
        Mail::to($user->email)->send(new SendOTPCode($code));

        return back()->with('status', 'A new verification code has been sent to your email.');
    }
}
