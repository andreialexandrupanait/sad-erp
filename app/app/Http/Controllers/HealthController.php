<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

/**
 * Health Check Controller
 *
 * Provides system health status for monitoring and load balancers.
 * Returns 200 for healthy/degraded, 503 for unhealthy.
 */
class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $status = 'healthy';
        $checks = [];

        // Database connectivity
        try {
            $latency = $this->measureLatency(fn() => DB::connection()->getPdo());
            $checks['database'] = [
                'status' => 'ok',
                'latency_ms' => $latency,
            ];
        } catch (\Exception $e) {
            Log::error('Health check failed: database', [
                'error' => $e->getMessage(),
                'check_type' => 'database',
            ]);
            $status = 'unhealthy';
            $checks['database'] = [
                'status' => 'error',
                'error' => 'Connection failed',
            ];
        }

        // Redis/Cache connectivity
        try {
            Cache::store('redis')->put('health_check', time(), 5);
            Cache::store('redis')->forget('health_check');
            $checks['cache'] = ['status' => 'ok'];
        } catch (\Exception $e) {
            Log::warning('Health check failed: cache/redis', [
                'error' => $e->getMessage(),
                'check_type' => 'cache',
            ]);
            $status = $this->degradeStatus($status);
            $checks['cache'] = [
                'status' => 'error',
                'error' => 'Connection failed',
            ];
        }

        // Queue connectivity (if not sync)
        if (config('queue.default') !== 'sync') {
            try {
                Queue::connection()->size('default');
                $checks['queue'] = ['status' => 'ok'];
            } catch (\Exception $e) {
                Log::warning('Health check failed: queue', [
                    'error' => $e->getMessage(),
                    'check_type' => 'queue',
                    'queue_driver' => config('queue.default'),
                ]);
                $status = $this->degradeStatus($status);
                $checks['queue'] = [
                    'status' => 'error',
                    'error' => 'Connection failed',
                ];
            }
        }

        // Disk space check
        $diskPath = storage_path();
        $freeSpace = @disk_free_space($diskPath);
        $totalSpace = @disk_total_space($diskPath);

        if ($freeSpace !== false && $totalSpace !== false && $totalSpace > 0) {
            $freePercent = ($freeSpace / $totalSpace) * 100;

            if ($freePercent < 5) {
                $status = 'unhealthy';
                $checks['disk'] = [
                    'status' => 'critical',
                    'free_percent' => round($freePercent, 2),
                    'free_gb' => round($freeSpace / 1024 / 1024 / 1024, 2),
                ];
            } elseif ($freePercent < 15) {
                $status = $this->degradeStatus($status);
                $checks['disk'] = [
                    'status' => 'warning',
                    'free_percent' => round($freePercent, 2),
                    'free_gb' => round($freeSpace / 1024 / 1024 / 1024, 2),
                ];
            } else {
                $checks['disk'] = [
                    'status' => 'ok',
                    'free_percent' => round($freePercent, 2),
                ];
            }
        }

        // Determine HTTP status code
        $httpStatus = match ($status) {
            'healthy' => 200,
            'degraded' => 200,
            'unhealthy' => 503,
        };

        return response()->json([
            'status' => $status,
            'timestamp' => now()->toIso8601String(),
            'version' => config('app.version', '1.0.0'),
            'checks' => $checks,
        ], $httpStatus);
    }

    /**
     * Measure execution latency of a callable.
     */
    private function measureLatency(callable $fn): float
    {
        $start = microtime(true);
        $fn();

        return round((microtime(true) - $start) * 1000, 2);
    }

    /**
     * Degrade status from healthy to degraded (but not from unhealthy).
     */
    private function degradeStatus(string $current): string
    {
        return $current === 'healthy' ? 'degraded' : $current;
    }
}
