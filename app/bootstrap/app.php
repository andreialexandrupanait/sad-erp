<?php

use App\Events\System\SystemErrorOccurred;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Trust all proxies for HTTPS detection (behind nginx-proxy)
        // Use HEADER_X_FORWARDED_ALL but exclude port to avoid :8080 appearing in URLs
        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_FOR |
                     Request::HEADER_X_FORWARDED_HOST |
                     Request::HEADER_X_FORWARDED_PROTO
        );

        // Set application locale based on settings
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\AuditLogger::class,
            \App\Http\Middleware\SecurityHeaders::class,
        ]);

        // Register middleware aliases
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            'module' => \App\Http\Middleware\CheckModuleAccess::class,
            'org' => \App\Http\Middleware\EnsureOrganizationScope::class,
            'require.password.confirmation' => \App\Http\Middleware\RequirePasswordConfirmation::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Report exceptions to notification system
        $exceptions->report(function (Throwable $e) {
            // Only report if notifications are enabled and exception reporting is enabled
            if (!config('notifications.enabled') || !config('notifications.notify_on_exceptions')) {
                return;
            }

            // Don't report certain exception types
            $dontReport = [
                \Illuminate\Auth\AuthenticationException::class,
                \Illuminate\Auth\Access\AuthorizationException::class,
                \Symfony\Component\HttpKernel\Exception\HttpException::class,
                \Illuminate\Database\Eloquent\ModelNotFoundException::class,
                \Illuminate\Session\TokenMismatchException::class,
                \Illuminate\Validation\ValidationException::class,
            ];

            foreach ($dontReport as $type) {
                if ($e instanceof $type) {
                    return;
                }
            }

            // Determine severity based on exception type
            $severity = 'error';
            if ($e instanceof \ErrorException) {
                $severity = match ($e->getSeverity()) {
                    E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR => 'critical',
                    E_WARNING, E_CORE_WARNING, E_COMPILE_WARNING, E_USER_WARNING => 'warning',
                    default => 'error',
                };
            }

            // Check if severity meets threshold
            $threshold = config('notifications.exception_threshold', 'error');
            $severityLevels = ['warning' => 1, 'error' => 2, 'critical' => 3, 'alert' => 4, 'emergency' => 5];
            $currentLevel = $severityLevels[$severity] ?? 2;
            $thresholdLevel = $severityLevels[$threshold] ?? 2;

            if ($currentLevel < $thresholdLevel) {
                return;
            }

            // Build context
            $context = [];
            $request = request();
            if ($request) {
                $context['url'] = $request->fullUrl();
                $context['method'] = $request->method();
                $context['ip'] = $request->ip();
                $context['user_agent'] = $request->userAgent();

                if ($request->user()) {
                    $context['user_id'] = $request->user()->id;
                    $context['user_email'] = $request->user()->email;
                }
            }

            try {
                event(new SystemErrorOccurred($e, $severity, $context));
            } catch (\Throwable $notificationException) {
                // Log but don't throw - we don't want notification failures to cause more issues
                Log::warning('Failed to dispatch SystemErrorOccurred event', [
                    'original_exception' => get_class($e),
                    'notification_error' => $notificationException->getMessage(),
                ]);
            }
        });
    })->create();
