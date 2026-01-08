<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureOrganizationScope
{
    /**
     * Handle an incoming request.
     *
     * Ensures that authenticated users belong to an organization.
     * This is a security layer to prevent users without organizations
     * from accessing protected resources.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->organization_id === null) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => __('Your account is not associated with any organization.'),
                ], 403);
            }

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('error', __('Your account is not associated with any organization. Please contact an administrator.'));
        }

        return $next($request);
    }
}
