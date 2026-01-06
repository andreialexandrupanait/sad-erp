<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Metrics Service
 *
 * Provides centralized metrics recording for observability.
 * Tracks key performance indicators that help with:
 * - API latency monitoring
 * - Error rate tracking
 * - Active user counts
 * - Operation duration logging
 *
 * All metrics are logged to a dedicated 'metrics' channel in JSON format
 * for easy parsing by log aggregation systems.
 */
class MetricsService
{
    /**
     * Record API endpoint latency.
     *
     * @param string $endpoint The API endpoint path
     * @param float $durationMs Duration in milliseconds
     * @param int|null $statusCode HTTP status code
     * @param string|null $method HTTP method (GET, POST, etc.)
     */
    public function recordApiLatency(
        string $endpoint,
        float $durationMs,
        ?int $statusCode = null,
        ?string $method = null
    ): void {
        Log::channel('metrics')->info('api_latency', [
            'metric_type' => 'api_latency',
            'endpoint' => $endpoint,
            'method' => $method,
            'duration_ms' => round($durationMs, 2),
            'status_code' => $statusCode,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Record operation success/failure for error rate tracking.
     *
     * @param string $operation The operation type (e.g., 'smartbill_import', 'bt_api_call')
     * @param bool $success Whether the operation succeeded
     * @param string|null $errorType Error category if failed
     */
    public function recordOperationResult(
        string $operation,
        bool $success,
        ?string $errorType = null
    ): void {
        Log::channel('metrics')->info('operation_result', [
            'metric_type' => 'operation_result',
            'operation' => $operation,
            'success' => $success,
            'error_type' => $errorType,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Record active user count metric.
     *
     * Call this periodically (e.g., via scheduler) to track concurrent users.
     */
    public function recordActiveUsers(): void
    {
        try {
            $count = Cache::remember('metrics.active_users_count', 60, function () {
                return DB::table('sessions')
                    ->where('last_activity', '>', now()->subMinutes(15)->timestamp)
                    ->whereNotNull('user_id')
                    ->distinct('user_id')
                    ->count('user_id');
            });

            Log::channel('metrics')->info('active_users', [
                'metric_type' => 'active_users',
                'count' => $count,
                'window_minutes' => 15,
                'timestamp' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to record active users metric', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Record a timed operation and return its duration.
     *
     * Usage:
     *   $timer = $metricsService->startTimer();
     *   // ... do operation ...
     *   $metricsService->recordTimedOperation('operation_name', $timer);
     *
     * @param string $operation Operation name
     * @param float $startTime Start time from microtime(true)
     * @param array $context Additional context to log
     * @return float Duration in milliseconds
     */
    public function recordTimedOperation(
        string $operation,
        float $startTime,
        array $context = []
    ): float {
        $durationMs = (microtime(true) - $startTime) * 1000;

        Log::channel('metrics')->info('operation_timing', array_merge([
            'metric_type' => 'operation_timing',
            'operation' => $operation,
            'duration_ms' => round($durationMs, 2),
            'timestamp' => now()->toIso8601String(),
        ], $context));

        return $durationMs;
    }

    /**
     * Get current timestamp for timing operations.
     *
     * @return float Timestamp from microtime(true)
     */
    public function startTimer(): float
    {
        return microtime(true);
    }

    /**
     * Record queue job metrics.
     *
     * @param string $jobClass The job class name
     * @param float $durationMs Processing duration
     * @param bool $success Whether job succeeded
     * @param int|null $attempts Number of attempts
     */
    public function recordQueueJob(
        string $jobClass,
        float $durationMs,
        bool $success,
        ?int $attempts = null
    ): void {
        Log::channel('metrics')->info('queue_job', [
            'metric_type' => 'queue_job',
            'job' => class_basename($jobClass),
            'duration_ms' => round($durationMs, 2),
            'success' => $success,
            'attempts' => $attempts,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Record database query count and duration.
     *
     * Useful for tracking N+1 queries and slow pages.
     *
     * @param int $queryCount Number of queries executed
     * @param float $totalDurationMs Total query duration
     * @param string|null $context Request context (e.g., route name)
     */
    public function recordDatabaseMetrics(
        int $queryCount,
        float $totalDurationMs,
        ?string $context = null
    ): void {
        Log::channel('metrics')->info('database_metrics', [
            'metric_type' => 'database_metrics',
            'query_count' => $queryCount,
            'total_duration_ms' => round($totalDurationMs, 2),
            'context' => $context,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Record memory usage metric.
     *
     * Useful for tracking memory-intensive operations.
     *
     * @param string $operation Operation name
     * @param int|null $peakMemory Peak memory in bytes (defaults to current peak)
     */
    public function recordMemoryUsage(string $operation, ?int $peakMemory = null): void
    {
        $peak = $peakMemory ?? memory_get_peak_usage(true);

        Log::channel('metrics')->info('memory_usage', [
            'metric_type' => 'memory_usage',
            'operation' => $operation,
            'peak_memory_mb' => round($peak / 1024 / 1024, 2),
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
