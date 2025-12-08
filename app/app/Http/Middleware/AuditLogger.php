<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AuditLogger
{
    /**
     * HTTP methods that should be logged (state-changing operations).
     */
    protected array $logMethods = ['POST', 'PUT', 'PATCH', 'DELETE'];

    /**
     * Handle an incoming request.
     *
     * Logs state-changing requests for audit purposes.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($this->shouldLog($request, $response)) {
            $this->logRequest($request, $response);
        }

        return $response;
    }

    /**
     * Determine if the request should be logged.
     */
    protected function shouldLog(Request $request, Response $response): bool
    {
        return in_array($request->method(), $this->logMethods)
            && Auth::check()
            && $response->isSuccessful();
    }

    /**
     * Log the request details.
     */
    protected function logRequest(Request $request, Response $response): void
    {
        $user = Auth::user();

        Log::channel('audit')->info('User action', [
            'user_id' => $user->id,
            'organization_id' => $user->organization_id,
            'method' => $request->method(),
            'path' => $request->path(),
            'route' => $request->route()?->getName(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'status' => $response->getStatusCode(),
        ]);
    }
}
