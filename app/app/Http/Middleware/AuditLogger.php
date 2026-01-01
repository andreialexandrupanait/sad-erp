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
        // Log all state-changing requests (both success and failure) for security auditing
        return in_array($request->method(), $this->logMethods)
            && Auth::check();
    }

    /**
     * Log the request details.
     */
    protected function logRequest(Request $request, Response $response): void
    {
        $user = Auth::user();
        $statusCode = $response->getStatusCode();
        $isSuccess = $response->isSuccessful();

        // Determine log level based on response status
        $logLevel = $isSuccess ? 'info' : ($statusCode >= 500 ? 'error' : 'warning');

        $logData = [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'organization_id' => $user->organization_id,
            'method' => $request->method(),
            'path' => $request->path(),
            'route' => $request->route()?->getName(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'status' => $statusCode,
            'success' => $isSuccess,
        ];

        // Add failure context for non-successful requests
        if (!$isSuccess) {
            $logData['failure_type'] = match(true) {
                $statusCode === 401 => 'unauthorized',
                $statusCode === 403 => 'forbidden',
                $statusCode === 404 => 'not_found',
                $statusCode === 422 => 'validation_failed',
                $statusCode >= 500 => 'server_error',
                default => 'client_error',
            };
        }

        Log::channel('audit')->$logLevel('User action', $logData);
    }
}
