<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Require2FA
{
    /**
     * Handle an incoming request.
     *
     * Redirects users with 2FA enabled to the challenge page if not yet verified.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // If user has 2FA enabled but hasn't verified this session, redirect to challenge
        if ($user && $user->hasTwoFactorEnabled() && !$request->session()->get('2fa_verified')) {
            // Store intended URL for redirect after 2FA
            if (!$request->session()->has('url.intended')) {
                $request->session()->put('url.intended', $request->url());
            }

            return redirect()->route('2fa.challenge');
        }

        return $next($request);
    }
}
