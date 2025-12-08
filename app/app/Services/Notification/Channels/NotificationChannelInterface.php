<?php

namespace App\Services\Notification\Channels;

use App\Services\Notification\Messages\NotificationMessage;

interface NotificationChannelInterface
{
    /**
     * Send a notification message through this channel.
     *
     * @param NotificationMessage $message The message to send
     * @return bool True if sent successfully, false otherwise
     * @throws \Exception If sending fails
     */
    public function send(NotificationMessage $message): bool;

    /**
     * Check if this channel is properly configured and ready to use.
     *
     * @return bool True if configured, false otherwise
     */
    public function isConfigured(): bool;

    /**
     * Get the unique name of this channel.
     *
     * @return string Channel name (e.g., 'slack', 'whatsapp')
     */
    public function getName(): string;

    /**
     * Check if this channel is enabled in configuration.
     *
     * @return bool True if enabled, false otherwise
     */
    public function isEnabled(): bool;
}
