<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Jenssegers\Agent\Agent;

class SessionController extends Controller
{
    /**
     * Get all sessions for the current user
     */
    public function index(Request $request)
    {
        $sessions = $this->getSessions($request);

        return view('profile.sessions', [
            'sessions' => $sessions,
            'currentSessionId' => $request->session()->getId(),
        ]);
    }

    /**
     * Logout from a specific session
     */
    public function destroy(Request $request, string $sessionId)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        if (!Hash::check($request->password, $request->user()->password)) {
            throw ValidationException::withMessages([
                'password' => 'The provided password is incorrect.',
            ]);
        }

        // Prevent deleting current session through this method
        if ($sessionId === $request->session()->getId()) {
            return back()->with('error', 'Cannot logout current session. Use the logout button instead.');
        }

        DB::table('sessions')
            ->where('id', $sessionId)
            ->where('user_id', $request->user()->id)
            ->delete();

        return back()->with('success', 'Session has been terminated.');
    }

    /**
     * Logout from all other sessions
     */
    public function destroyOthers(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        if (!Hash::check($request->password, $request->user()->password)) {
            throw ValidationException::withMessages([
                'password' => 'The provided password is incorrect.',
            ]);
        }

        DB::table('sessions')
            ->where('user_id', $request->user()->id)
            ->where('id', '!=', $request->session()->getId())
            ->delete();

        return back()->with('success', 'All other sessions have been terminated.');
    }

    /**
     * Get formatted sessions list
     */
    protected function getSessions(Request $request): array
    {
        $sessions = DB::table('sessions')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('last_activity')
            ->get();

        return $sessions->map(function ($session) use ($request) {
            $agent = $this->createAgent($session);

            return (object) [
                'id' => $session->id,
                'ip_address' => $session->ip_address,
                'is_current' => $session->id === $request->session()->getId(),
                'last_active' => Carbon::createFromTimestamp($session->last_activity)->diffForHumans(),
                'device' => $this->getDeviceInfo($agent),
                'browser' => $agent->browser() ?: 'Unknown',
                'platform' => $agent->platform() ?: 'Unknown',
                'is_desktop' => $agent->isDesktop(),
                'is_mobile' => $agent->isMobile(),
                'is_tablet' => $agent->isTablet(),
            ];
        })->toArray();
    }

    /**
     * Create agent from session user_agent
     */
    protected function createAgent($session): Agent
    {
        $agent = new Agent();
        $agent->setUserAgent($session->user_agent);

        return $agent;
    }

    /**
     * Get device description
     */
    protected function getDeviceInfo(Agent $agent): string
    {
        $device = $agent->device();

        if ($agent->isDesktop()) {
            return $device ?: 'Desktop';
        }

        if ($agent->isMobile()) {
            return $device ?: 'Mobile Device';
        }

        if ($agent->isTablet()) {
            return $device ?: 'Tablet';
        }

        return $device ?: 'Unknown Device';
    }
}
