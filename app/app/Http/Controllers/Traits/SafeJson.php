<?php

namespace App\Http\Controllers\Traits;

use JsonException;

/**
 * Trait for safe JSON encoding/decoding with proper error handling.
 *
 * Provides methods that handle JSON operations safely, avoiding silent failures
 * and ensuring proper error reporting.
 */
trait SafeJson
{
    /**
     * Safely decode JSON string to array.
     *
     * @param string|null $json The JSON string to decode
     * @param array $default Default value if decoding fails
     * @return array The decoded array or default value
     */
    protected function safeJsonDecode(?string $json, array $default = []): array
    {
        if (empty($json)) {
            return $default;
        }

        try {
            $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
            return is_array($decoded) ? $decoded : $default;
        } catch (JsonException $e) {
            report($e);
            return $default;
        }
    }

    /**
     * Safely encode array to JSON string.
     *
     * @param mixed $data The data to encode
     * @param int $flags JSON encoding flags
     * @return string|null The JSON string or null on failure
     */
    protected function safeJsonEncode(mixed $data, int $flags = 0): ?string
    {
        try {
            return json_encode($data, $flags | JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            report($e);
            return null;
        }
    }

    /**
     * Ensure value is an array, decoding from JSON if necessary.
     *
     * @param mixed $value The value to normalize (can be string, array, or null)
     * @param array $default Default value if normalization fails
     * @return array The normalized array
     */
    protected function ensureArray(mixed $value, array $default = []): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value)) {
            return $this->safeJsonDecode($value, $default);
        }

        return $default;
    }
}
