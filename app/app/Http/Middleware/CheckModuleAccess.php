<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckModuleAccess
{
    /**
     * Module slug to route prefix mapping.
     */
    protected array $routeModuleMap = [
        'clients' => 'clients',
        'domains' => 'domains',
        'subscriptions' => 'subscriptions',
        'credentials' => 'credentials',
        'financial' => 'finance',
        'internal-accounts' => 'internal_accounts',
        'analytics' => 'analytics',
        'settings' => 'settings',
        'dashboard' => 'dashboard',
    ];

    /**
     * HTTP method to action mapping.
     */
    protected array $methodActionMap = [
        'GET' => 'view',
        'POST' => 'create',
        'PUT' => 'update',
        'PATCH' => 'update',
        'DELETE' => 'delete',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ?string $module = null, ?string $action = null): Response
    {
        $user = $request->user();

        if (!$user) {
            return $this->unauthorized($request, __('auth.unauthenticated'));
        }

        // Super admin and org admin bypass module checks
        if ($user->isSuperAdmin() || $user->isOrgAdmin()) {
            return $next($request);
        }

        // Determine module from parameter or route
        $moduleSlug = $module ?? $this->getModuleFromRoute($request);

        if (!$moduleSlug) {
            return $next($request); // No module restriction
        }

        // Determine action from parameter or HTTP method
        $actionType = $action ?? $this->methodActionMap[$request->method()] ?? 'view';

        // Check permission
        if (!$user->canAccessModule($moduleSlug, $actionType)) {
            return $this->unauthorized(
                $request,
                __('messages.no_module_access', ['action' => $actionType, 'module' => $moduleSlug])
            );
        }

        return $next($request);
    }

    /**
     * Get module slug from route name.
     */
    protected function getModuleFromRoute(Request $request): ?string
    {
        $routeName = $request->route()?->getName();

        if (!$routeName) {
            return null;
        }

        // Extract first segment: 'clients.index' -> 'clients'
        $prefix = explode('.', $routeName)[0] ?? null;

        return $this->routeModuleMap[$prefix] ?? $prefix;
    }

    /**
     * Return unauthorized response.
     */
    protected function unauthorized(Request $request, string $message): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 403);
        }

        abort(403, $message);
    }
}
