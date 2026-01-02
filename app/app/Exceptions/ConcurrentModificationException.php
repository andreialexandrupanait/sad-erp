<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Exception thrown when concurrent modification is detected.
 *
 * This exception is used with optimistic locking to detect when two users
 * try to edit the same resource simultaneously. The second user to save
 * will receive this exception.
 */
class ConcurrentModificationException extends Exception
{
    /**
     * The HTTP status code for this exception.
     */
    protected int $statusCode = 409; // HTTP 409 Conflict

    /**
     * Create a new exception instance.
     */
    public function __construct(string $message = null, int $code = 0, ?Exception $previous = null)
    {
        parent::__construct(
            $message ?? __('The resource was modified by another user. Please refresh and try again.'),
            $code,
            $previous
        );
    }

    /**
     * Render the exception as an HTTP response.
     */
    public function render(Request $request): JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $this->getMessage(),
                'error' => 'concurrent_modification',
                'requires_refresh' => true,
            ], $this->statusCode);
        }

        return response()->json([
            'error' => $this->getMessage(),
        ], $this->statusCode);
    }

    /**
     * Report the exception.
     */
    public function report(): bool
    {
        // Don't report to error tracking - this is expected behavior
        return false;
    }
}
