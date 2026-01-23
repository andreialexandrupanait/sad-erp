<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Security Headers Middleware
 *
 * Adds security-related HTTP headers to all responses to protect against
 * common web vulnerabilities like XSS, clickjacking, MIME sniffing, etc.
 *
 * Headers added:
 * - X-Content-Type-Options: Prevents MIME sniffing
 * - X-Frame-Options: Prevents clickjacking
 * - Referrer-Policy: Controls referrer information
 * - Permissions-Policy: Controls browser features
 * - Content-Security-Policy: Controls resource loading
 * - Strict-Transport-Security: Enforces HTTPS (only in production)
 */
class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Generate a unique nonce for this request
        $nonce = base64_encode(random_bytes(16));
        $request->attributes->set('csp_nonce', $nonce);

        $response = $next($request);

        // Prevent MIME sniffing attacks
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Prevent clickjacking attacks
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Control referrer information leakage
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Disable potentially dangerous browser features
        $response->headers->set(
            'Permissions-Policy',
            'geolocation=(), microphone=(), camera=(), payment=(), usb=(), magnetometer=(), gyroscope=()'
        );

        // Content Security Policy
        // Note: Nonces removed from script-src because they make 'unsafe-inline' ignored per CSP spec.
        // Alpine.js requires 'unsafe-eval' for its expression evaluation (x-data, @click, etc.)
        // TODO: Consider migrating to @alpinejs/csp build for stricter security
        $websocketUrl = config('app.websocket_url', 'wss://localhost:6001');

        $csp = [
            "default-src 'self'",
            // script-src: 'unsafe-inline' needed for Alpine event handlers (@click, @blur, etc.)
            // 'unsafe-eval' needed for Alpine.js expression evaluation (x-data objects, etc.)
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.tailwindcss.com https://cdn.jsdelivr.net https://cdn.quilljs.com https://unpkg.com https://cdnjs.cloudflare.com https://cdn.tiny.cloud",
            // style-src: 'unsafe-inline' required for Tailwind and dynamic styles
            "style-src 'self' 'unsafe-inline' https://fonts.bunny.net https://cdn.quilljs.com https://cdn.jsdelivr.net https://unpkg.com https://cdnjs.cloudflare.com",
            "img-src 'self' data: https:",
            "font-src 'self' data: https://fonts.bunny.net",
            "connect-src 'self' https://cdn.quilljs.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://cdn.tiny.cloud https://unpkg.com {$websocketUrl} https://api.openapi.ro",
            "frame-ancestors 'self'",
            "base-uri 'self'",
            "form-action 'self'",
            "object-src 'none'",
        ];

        // SECURITY: CSP is now enforced by default (was report-only)
        // Set CSP_ENFORCE=false in .env to temporarily disable during debugging
        $enforceCSP = config('app.csp_enforce', true);

        // upgrade-insecure-requests only works in enforced mode, not report-only
        if ($enforceCSP) {
            $csp[] = "upgrade-insecure-requests";
        }

        $cspHeader = $enforceCSP
            ? 'Content-Security-Policy'
            : 'Content-Security-Policy-Report-Only';

        $response->headers->set($cspHeader, implode('; ', $csp));

        // HSTS (HTTP Strict Transport Security) - only in production with HTTPS
        if (config('app.env') === 'production' && $request->secure()) {
            // max-age=31536000 = 1 year
            // includeSubDomains = apply to all subdomains
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains; preload'
            );
        }

        return $response;
    }
}
