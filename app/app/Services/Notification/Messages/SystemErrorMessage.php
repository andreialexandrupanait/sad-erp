<?php

namespace App\Services\Notification\Messages;

use Throwable;

class SystemErrorMessage extends NotificationMessage
{
    public function __construct(
        protected Throwable $exception,
        protected string $severity = 'error',
        protected array $context = []
    ) {}

    public function getTitle(): string
    {
        $exceptionName = class_basename($this->exception);
        return "System Error: {$exceptionName}";
    }

    public function getBody(): string
    {
        $message = $this->exception->getMessage();
        $file = $this->exception->getFile();
        $line = $this->exception->getLine();

        $body = "{$message}\n\nFile: {$file}:{$line}";

        if (isset($this->context['url'])) {
            $body .= "\nURL: {$this->context['url']}";
        }

        return $body;
    }

    public function getPriority(): string
    {
        return match ($this->severity) {
            'critical', 'alert', 'emergency' => 'urgent',
            'error' => 'high',
            'warning' => 'normal',
            default => 'normal',
        };
    }

    public function getNotificationType(): string
    {
        return 'system_error';
    }

    public function getEntityType(): ?string
    {
        return null; // System errors are not tied to a specific entity
    }

    public function getEntityId(): ?int
    {
        return null;
    }

    public function toArray(): array
    {
        return [
            'exception_class' => get_class($this->exception),
            'exception_name' => class_basename($this->exception),
            'message' => $this->exception->getMessage(),
            'file' => $this->exception->getFile(),
            'line' => $this->exception->getLine(),
            'severity' => $this->severity,
            'context' => $this->context,
            'trace' => $this->getShortTrace(),
        ];
    }

    public function getFields(): array
    {
        $fields = [];

        $fields[] = [
            'title' => 'Error Type',
            'value' => class_basename($this->exception),
            'short' => true,
        ];

        $fields[] = [
            'title' => 'Severity',
            'value' => ucfirst($this->severity),
            'short' => true,
        ];

        $fields[] = [
            'title' => 'File',
            'value' => $this->getShortFilePath(),
            'short' => true,
        ];

        $fields[] = [
            'title' => 'Line',
            'value' => (string) $this->exception->getLine(),
            'short' => true,
        ];

        if (isset($this->context['url'])) {
            $fields[] = [
                'title' => 'URL',
                'value' => $this->context['url'],
                'short' => false,
            ];
        }

        if (isset($this->context['method'])) {
            $fields[] = [
                'title' => 'Method',
                'value' => $this->context['method'],
                'short' => true,
            ];
        }

        if (isset($this->context['user_id'])) {
            $fields[] = [
                'title' => 'User ID',
                'value' => (string) $this->context['user_id'],
                'short' => true,
            ];
        }

        return $fields;
    }

    public function getUrl(): ?string
    {
        // Link to error tracking system if configured
        return null;
    }

    public function getColor(): string
    {
        return match ($this->severity) {
            'critical', 'alert', 'emergency' => '#b71c1c', // Dark red
            'error' => '#d32f2f', // Red
            'warning' => '#ff9800', // Orange
            default => '#ff9800',
        };
    }

    public function getIcon(): string
    {
        return match ($this->severity) {
            'critical', 'alert', 'emergency' => ':rotating_light:',
            'error' => ':x:',
            'warning' => ':warning:',
            default => ':warning:',
        };
    }

    public function getFooter(): string
    {
        return 'ERP System - Error Monitoring';
    }

    /**
     * System errors should not be deduplicated by interval.
     * Each error is unique and should be reported.
     */
    public function isIntervalBased(): bool
    {
        return false;
    }

    /**
     * Get a shortened file path for display.
     */
    protected function getShortFilePath(): string
    {
        $file = $this->exception->getFile();

        // Remove the base path if present
        $basePath = base_path();
        if (str_starts_with($file, $basePath)) {
            return substr($file, strlen($basePath) + 1);
        }

        return $file;
    }

    /**
     * Get a shortened stack trace.
     */
    protected function getShortTrace(): string
    {
        $trace = $this->exception->getTraceAsString();

        // Limit to first 1000 characters
        if (strlen($trace) > 1000) {
            return substr($trace, 0, 1000) . '...';
        }

        return $trace;
    }
}
