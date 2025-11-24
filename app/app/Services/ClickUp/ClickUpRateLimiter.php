<?php

namespace App\Services\ClickUp;

use Illuminate\Support\Facades\Log;

class ClickUpRateLimiter
{
    protected $limit;
    protected $remaining;
    protected $resetAt;

    /**
     * Check rate limit headers from ClickUp API response and throttle if needed
     *
     * @param \Illuminate\Http\Client\Response $response
     * @return void
     */
    public function checkHeaders($response)
    {
        $this->limit = $response->header('X-RateLimit-Limit');
        $this->remaining = $response->header('X-RateLimit-Remaining');
        $this->resetAt = $response->header('X-RateLimit-Reset');

        Log::debug('ClickUp Rate Limit Status', [
            'limit' => $this->limit,
            'remaining' => $this->remaining,
            'reset_at' => $this->resetAt ? date('Y-m-d H:i:s', $this->resetAt) : null,
        ]);

        // If approaching limit (less than 10 requests remaining), wait for reset
        if ($this->remaining !== null && $this->remaining < 10 && $this->resetAt) {
            $waitSeconds = max($this->resetAt - time(), 0);

            if ($waitSeconds > 0) {
                Log::warning('ClickUp rate limit approaching, throttling', [
                    'remaining' => $this->remaining,
                    'waiting_seconds' => $waitSeconds,
                ]);

                sleep($waitSeconds + 1); // Add 1 second buffer
            }
        }
    }

    /**
     * Get the current limit
     *
     * @return int|null
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * Get remaining requests
     *
     * @return int|null
     */
    public function getRemaining()
    {
        return $this->remaining;
    }

    /**
     * Get reset timestamp
     *
     * @return int|null
     */
    public function getResetAt()
    {
        return $this->resetAt;
    }

    /**
     * Check if we should throttle based on remaining requests
     *
     * @param int $threshold
     * @return bool
     */
    public function shouldThrottle($threshold = 10)
    {
        return $this->remaining !== null && $this->remaining < $threshold;
    }
}
