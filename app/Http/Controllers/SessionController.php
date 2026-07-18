<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SessionController extends Controller
{
    /**
     * List the current user's active sessions (requires SESSION_DRIVER=database).
     */
    public function index(Request $request)
    {
        $currentSessionId = $request->session()->getId();

        $sessions = DB::table('sessions')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('last_activity')
            ->get()
            ->map(function ($session) use ($currentSessionId) {
                $agent = $this->parseUserAgent($session->user_agent);

                return [
                    'id'                => $session->id,
                    'is_current_device' => $session->id === $currentSessionId,
                    'ip_address'        => $session->ip_address,
                    'browser'           => $agent['browser'],
                    'platform'          => $agent['platform'],
                    'last_active'       => Carbon::createFromTimestamp($session->last_activity)->diffForHumans(),
                ];
            })
            ->values();

        return response()->json(['sessions' => $sessions]);
    }

    /**
     * Log the user out of every session except the one making this request.
     *
     * Deleting the rows here is immediate for session listing purposes; the
     * other browser tabs/devices themselves get force-logged-out on their
     * next request via the AuthenticateSession middleware, which compares
     * each session's stored password hash against the user's current one.
     */
    public function destroyOthers(Request $request)
    {
        $currentSessionId = $request->session()->getId();

        DB::table('sessions')
            ->where('user_id', $request->user()->id)
            ->where('id', '!=', $currentSessionId)
            ->delete();

        return redirect()->back()->with(['status' => 'ok']);
    }

    /**
     * Very small, dependency-free user-agent parser. No UA-parsing package
     * is installed in this project (composer.json has none, and this
     * environment has no network access to add one) — this covers the
     * common desktop/mobile browsers well enough for display purposes,
     * but isn't as thorough as a dedicated library like jenssegers/agent.
     *
     * @return array{browser: string, platform: string}
     */
    private function parseUserAgent(?string $userAgent): array
    {
        $userAgent = $userAgent ?? '';

        $platform = match (true) {
            (bool) preg_match('/iPhone|iPad|iPod/i', $userAgent) => 'iOS',
            (bool) preg_match('/Android/i', $userAgent) => 'Android',
            (bool) preg_match('/Macintosh|Mac OS X/i', $userAgent) => 'Mac',
            (bool) preg_match('/Windows/i', $userAgent) => 'Windows',
            (bool) preg_match('/Linux/i', $userAgent) => 'Linux',
            default => 'Unknown device',
        };

        // Order matters: Edge/Opera UAs also contain "Chrome", and Chrome
        // UAs also contain "Safari", so the more specific checks go first.
        $browser = match (true) {
            (bool) preg_match('/Edg\//i', $userAgent) => 'Edge',
            (bool) preg_match('/OPR\/|Opera/i', $userAgent) => 'Opera',
            (bool) preg_match('/Firefox\//i', $userAgent) => 'Firefox',
            (bool) preg_match('/CriOS\//i', $userAgent) => 'Chrome',
            (bool) preg_match('/Chrome\//i', $userAgent) => 'Chrome',
            (bool) preg_match('/Safari\//i', $userAgent) => 'Safari',
            default => 'Unknown browser',
        };

        return ['browser' => $browser, 'platform' => $platform];
    }
}
