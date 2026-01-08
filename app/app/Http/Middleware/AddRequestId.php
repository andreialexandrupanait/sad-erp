<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware that adds a unique request ID to all requests for log correlation.
 *
 * This enables tracing a single request across all log entries, which is essential
 * for debugging issues in production and correlating logs in distributed systems.
 *
 * The request ID is:
 * - Accepted from X-Request-ID header if provided (for distributed tracing)
 * - Generated as UUID if not provided
 * - Added to all subsequent log entries via Log::withContext()
 * - Returned in the response X-Request-ID header
 */
class AddRequestId
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Accept existing request ID from upstream proxy/service, or generate new one
        $requestId = $request->header('X-Request-ID') ?? Str::uuid()->toString();

        // Store request ID for later access
        $request->attributes->set('request_id', $requestId);

        // Add context to all subsequent log calls in this request
        Log::withContext([
            'request_id' => $requestId,
            'user_id' => auth()->id(),
            'organization_id' => auth()->user()?->organization_id,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'uri' => $request->getRequestUri(),
            'method' => $request->method(),
        ]);

        $response = $next($request);

        // Add request ID to response header for client-side correlation
        $response->headers->set('X-Request-ID', $requestId);

        return $response;
    }
}
