<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

/**
 * Require Password Confirmation Middleware
 *
 * This middleware ensures users must confirm their password before
 * performing sensitive operations like revealing stored passwords.
 *
 * Security features:
 * - Rate limiting: 5 attempts per 5 minutes per user
 * - Failed attempt logging
 * - Lockout message with remaining time
 *
 * Usage:
 * Route::post('credentials/{credential}/reveal-password', ...)
 *     ->middleware('require.password.confirmation');
 */
class RequirePasswordConfirmation
{
    /**
     * Maximum number of password confirmation attempts.
     */
    private const MAX_ATTEMPTS = 5;

    /**
     * Decay time in seconds (5 minutes).
     */
    private const DECAY_SECONDS = 300;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Rate limit key per user
        $key = 'password_confirm:' . auth()->id();

        // Check if user is rate limited
        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($key);

            \Log::warning('Password confirmation rate limit exceeded', [
                'user_id' => auth()->id(),
                'user_email' => auth()->user()->email,
                'ip_address' => $request->ip(),
                'seconds_remaining' => $seconds,
            ]);

            return response()->json([
                'success' => false,
                'message' => __('Too many confirmation attempts. Please try again in :seconds seconds.', ['seconds' => $seconds]),
                'rate_limited' => true,
                'retry_after' => $seconds,
            ], 429);
        }

        // Check if password confirmation was provided
        if (!$request->has('current_password')) {
            return response()->json([
                'success' => false,
                'message' => __('Password confirmation is required for this action.'),
                'requires_confirmation' => true,
            ], 403);
        }

        $currentPassword = $request->input('current_password');

        // Verify the password matches the authenticated user's password
        if (!Hash::check($currentPassword, auth()->user()->password)) {
            // Increment rate limiter on failed attempt
            RateLimiter::hit($key, self::DECAY_SECONDS);

            $attemptsRemaining = self::MAX_ATTEMPTS - RateLimiter::attempts($key);

            \Log::warning('Failed password confirmation attempt', [
                'user_id' => auth()->id(),
                'user_email' => auth()->user()->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'action' => $request->path(),
                'attempts_remaining' => $attemptsRemaining,
            ]);

            return response()->json([
                'success' => false,
                'message' => __('The provided password does not match your current password.'),
                'requires_confirmation' => true,
                'attempts_remaining' => max(0, $attemptsRemaining),
            ], 403);
        }

        // Clear rate limiter on successful confirmation
        RateLimiter::clear($key);

        // Log successful password confirmation
        \Log::info('Password confirmation successful', [
            'user_id' => auth()->id(),
            'action' => $request->path(),
            'ip_address' => $request->ip(),
        ]);

        return $next($request);
    }
}
