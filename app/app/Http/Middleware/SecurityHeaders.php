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
 * - X-XSS-Protection: Enables XSS filter in older browsers
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
        $response = $next($request);

        // Prevent MIME sniffing attacks
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Prevent clickjacking attacks
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Enable XSS protection in older browsers
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Control referrer information leakage
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Disable potentially dangerous browser features
        $response->headers->set(
            'Permissions-Policy',
            'geolocation=(), microphone=(), camera=(), payment=(), usb=(), magnetometer=(), gyroscope=()'
        );

        // Content Security Policy
        // Adjust these based on your application's actual needs
        $csp = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.tailwindcss.com https://cdn.jsdelivr.net", // Allow Tailwind CDN and Chart.js
            "style-src 'self' 'unsafe-inline' https://fonts.bunny.net", // Allow Google Fonts alternative
            "img-src 'self' data: https:",
            "font-src 'self' data: https://fonts.bunny.net", // Allow font files from Bunny Fonts
            "connect-src 'self'",
            "frame-ancestors 'self'",
            "base-uri 'self'",
            "form-action 'self'",
        ];

        $response->headers->set('Content-Security-Policy', implode('; ', $csp));

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
