<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

/**
 * Require Password Confirmation Middleware
 *
 * This middleware ensures users must confirm their password before
 * performing sensitive operations like revealing stored passwords.
 *
 * Usage:
 * Route::post('credentials/{credential}/reveal-password', ...)
 *     ->middleware('require.password.confirmation');
 */
class RequirePasswordConfirmation
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
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
            \Log::warning('Failed password confirmation attempt', [
                'user_id' => auth()->id(),
                'user_email' => auth()->user()->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'action' => $request->path(),
            ]);

            return response()->json([
                'success' => false,
                'message' => __('The provided password does not match your current password.'),
                'requires_confirmation' => true,
            ], 403);
        }

        // Log successful password confirmation
        \Log::info('Password confirmation successful', [
            'user_id' => auth()->id(),
            'action' => $request->path(),
            'ip_address' => $request->ip(),
        ]);

        return $next($request);
    }
}
