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
        // Generate a unique nonce for this request
        $nonce = base64_encode(random_bytes(16));
        $request->attributes->set('csp_nonce', $nonce);

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

        // Content Security Policy (Nonce-based)
        // Note: Currently in REPORT-ONLY mode to avoid breaking existing functionality
        // Gradually migrate inline scripts to use nonces, then switch to enforcing mode
        $csp = [
            "default-src 'self'",
            // script-src: Allow scripts from self, with nonce for inline scripts
            // Temporarily keeping unsafe-inline/unsafe-eval for backward compatibility
            "script-src 'self' 'nonce-{$nonce}' 'unsafe-inline' 'unsafe-eval' https://cdn.tailwindcss.com https://cdn.jsdelivr.net https://cdn.quilljs.com https://unpkg.com",
            // style-src: Allow styles from self, with nonce for inline styles
            "style-src 'self' 'nonce-{$nonce}' 'unsafe-inline' https://fonts.bunny.net https://cdn.quilljs.com https://cdn.jsdelivr.net https://unpkg.com",
            "img-src 'self' data: https:",
            "font-src 'self' data: https://fonts.bunny.net",
            "connect-src 'self' https://cdn.quilljs.com https://cdn.jsdelivr.net",
            "frame-ancestors 'self'",
            "base-uri 'self'",
            "form-action 'self'",
            "upgrade-insecure-requests",
        ];

        // Use Content-Security-Policy-Report-Only during migration phase
        // Switch to Content-Security-Policy once inline scripts are migrated
        $cspHeader = config('app.csp_enforce', false)
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
