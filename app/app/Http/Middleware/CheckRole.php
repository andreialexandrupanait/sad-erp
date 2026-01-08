<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * Check if the authenticated user has one of the required roles.
     * Usage: middleware('role:admin,manager') or middleware('role:admin')
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles  One or more role names to allow
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Must be authenticated
        if (!$request->user()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('login');
        }

        // Must have organization
        if (!$request->user()->organization_id) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'User must belong to an organization.'], 403);
            }
            abort(403, 'User must belong to an organization.');
        }

        // If no roles specified, just require authentication
        if (empty($roles)) {
            return $next($request);
        }

        // Superadmin bypasses all role checks
        if ($request->user()->role === 'superadmin') {
            return $next($request);
        }

        // Check if user has one of the required roles
        if (!in_array($request->user()->role, $roles, true)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'You do not have permission to access this resource.',
                    'required_roles' => $roles,
                ], 403);
            }
            abort(403, 'You do not have permission to access this resource.');
        }

        return $next($request);
    }
}
