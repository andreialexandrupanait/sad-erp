<?php

namespace App\Events\System;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Throwable;

class SystemErrorOccurred
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param Throwable $exception The exception that occurred
     * @param string $severity Severity level ('error', 'critical', 'warning')
     * @param array $context Additional context (url, method, user_id, ip, etc.)
     */
    public function __construct(
        public Throwable $exception,
        public string $severity = 'error',
        public array $context = []
    ) {}

    /**
     * Get the notification type.
     */
    public function getNotificationType(): string
    {
        return 'system_error';
    }

    /**
     * Get the exception class name (short form).
     */
    public function getExceptionName(): string
    {
        return class_basename($this->exception);
    }

    /**
     * Get the exception message.
     */
    public function getExceptionMessage(): string
    {
        return $this->exception->getMessage();
    }

    /**
     * Get the file where the exception occurred.
     */
    public function getFile(): string
    {
        return $this->exception->getFile();
    }

    /**
     * Get the line number where the exception occurred.
     */
    public function getLine(): int
    {
        return $this->exception->getLine();
    }

    /**
     * Get the stack trace.
     */
    public function getTrace(): string
    {
        return $this->exception->getTraceAsString();
    }
}
