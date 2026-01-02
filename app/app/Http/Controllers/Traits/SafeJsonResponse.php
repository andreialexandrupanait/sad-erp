<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * Trait for safe JSON error responses in controllers.
 *
 * This trait provides methods to handle exceptions safely without
 * exposing sensitive error details to clients while ensuring proper logging.
 *
 * Usage: Add `use SafeJsonResponse;` to your controller.
 */
trait SafeJsonResponse
{
    /**
     * Return a safe JSON error response without exposing exception details.
     *
     * Logs the full exception for debugging while returning a generic
     * user-friendly error message to the client.
     *
     * @param \Exception $e The caught exception
     * @param string $context Description of what operation failed (for logging)
     * @param int $statusCode HTTP status code (default 500)
     * @return JsonResponse
     */
    protected function safeJsonError(\Exception $e, string $context = 'Operation', int $statusCode = 500): JsonResponse
    {
        Log::error("{$context} failed", [
            'exception' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'user_id' => auth()->id(),
            'url' => request()->fullUrl(),
        ]);

        return response()->json([
            'success' => false,
            'message' => __('An error occurred. Please try again.'),
        ], $statusCode);
    }

    /**
     * Return a safe JSON validation error response.
     *
     * Use this for business logic validation failures that should
     * return a user-friendly message without logging as an error.
     *
     * @param string $message User-friendly error message
     * @param array $errors Optional validation errors array
     * @param int $statusCode HTTP status code (default 422)
     * @return JsonResponse
     */
    protected function safeJsonValidationError(string $message, array $errors = [], int $statusCode = 422): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return a safe JSON success response.
     *
     * Provides consistent success response format across all controllers.
     *
     * @param string $message Success message
     * @param array $data Optional data to include in response
     * @param int $statusCode HTTP status code (default 200)
     * @return JsonResponse
     */
    protected function safeJsonSuccess(string $message, array $data = [], int $statusCode = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if (!empty($data)) {
            $response = array_merge($response, $data);
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return a safe JSON not found response.
     *
     * @param string $resource Name of the resource that wasn't found
     * @return JsonResponse
     */
    protected function safeJsonNotFound(string $resource = 'Resource'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => __(':resource not found.', ['resource' => $resource]),
        ], 404);
    }

    /**
     * Return a safe JSON unauthorized response.
     *
     * @param string|null $message Optional custom message
     * @return JsonResponse
     */
    protected function safeJsonUnauthorized(?string $message = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message ?? __('You are not authorized to perform this action.'),
        ], 403);
    }

    /**
     * Return success response as JSON or redirect based on request type.
     *
     * Use this when an action can be triggered via AJAX or form submission.
     *
     * @param string $message Success message
     * @param array $data Additional data for JSON response
     * @param string|null $redirectTo Route or URL to redirect to (null = back)
     * @return JsonResponse|\Illuminate\Http\RedirectResponse
     */
    protected function respondSuccess(string $message, array $data = [], ?string $redirectTo = null)
    {
        if (request()->expectsJson()) {
            return $this->safeJsonSuccess($message, $data);
        }

        $redirect = $redirectTo ? redirect($redirectTo) : back();
        return $redirect->with('success', $message);
    }

    /**
     * Return error response as JSON or redirect based on request type.
     *
     * Use this when an action can be triggered via AJAX or form submission.
     *
     * @param string $message Error message
     * @param int $statusCode HTTP status code for JSON response
     * @param string|null $redirectTo Route or URL to redirect to (null = back)
     * @return JsonResponse|\Illuminate\Http\RedirectResponse
     */
    protected function respondError(string $message, int $statusCode = 400, ?string $redirectTo = null)
    {
        if (request()->expectsJson()) {
            return $this->safeJsonValidationError($message, [], $statusCode);
        }

        $redirect = $redirectTo ? redirect($redirectTo) : back();
        return $redirect->with('error', $message)->withInput();
    }

    /**
     * Handle exception and return appropriate response type.
     *
     * Logs the exception and returns either JSON or redirect with error.
     *
     * @param \Exception $e The caught exception
     * @param string $context Description of what operation failed
     * @param string|null $redirectTo Route or URL to redirect to (null = back)
     * @return JsonResponse|\Illuminate\Http\RedirectResponse
     */
    protected function respondException(\Exception $e, string $context = 'Operation', ?string $redirectTo = null)
    {
        Log::error("{$context} failed", [
            'exception' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'user_id' => auth()->id(),
        ]);

        if (request()->expectsJson()) {
            return $this->safeJsonError($e, $context);
        }

        $redirect = $redirectTo ? redirect($redirectTo) : back();
        return $redirect->with('error', __('An error occurred. Please try again.'))->withInput();
    }
}
