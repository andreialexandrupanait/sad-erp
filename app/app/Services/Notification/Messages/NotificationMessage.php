<?php

namespace App\Services\Notification\Messages;

abstract class NotificationMessage
{
    /**
     * Get the notification title/headline.
     *
     * @return string
     */
    abstract public function getTitle(): string;

    /**
     * Get the notification body/content.
     *
     * @return string
     */
    abstract public function getBody(): string;

    /**
     * Get the notification priority level.
     *
     * @return string One of: 'low', 'normal', 'high', 'urgent'
     */
    abstract public function getPriority(): string;

    /**
     * Get the notification type identifier.
     * Used for logging and user preference checking.
     *
     * @return string e.g., 'domain_expiring_7d', 'subscription_renewal_3d'
     */
    abstract public function getNotificationType(): string;

    /**
     * Get the entity type this notification is about.
     *
     * @return string|null e.g., 'Domain', 'Subscription', null for system notifications
     */
    abstract public function getEntityType(): ?string;

    /**
     * Get the entity ID this notification is about.
     *
     * @return int|null
     */
    abstract public function getEntityId(): ?int;

    /**
     * Convert the message to an array for logging/storage.
     *
     * @return array
     */
    abstract public function toArray(): array;

    /**
     * Get additional fields for the notification.
     * Used by formatters to add extra context.
     *
     * @return array Array of ['title' => 'Field Name', 'value' => 'Field Value', 'short' => bool]
     */
    public function getFields(): array
    {
        return [];
    }

    /**
     * Get a URL to view more details about this notification.
     *
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return null;
    }

    /**
     * Get the color for this notification (used in Slack attachments).
     * Default implementation maps priority to color.
     *
     * @return string Hex color code
     */
    public function getColor(): string
    {
        return match ($this->getPriority()) {
            'urgent' => '#d32f2f', // Red
            'high' => '#ff9800',   // Orange
            'normal' => '#2196f3', // Blue
            'low' => '#4caf50',    // Green
            default => '#9e9e9e',  // Gray
        };
    }

    /**
     * Get the icon/emoji for this notification.
     *
     * @return string
     */
    public function getIcon(): string
    {
        return match ($this->getPriority()) {
            'urgent' => ':rotating_light:',
            'high' => ':warning:',
            'normal' => ':bell:',
            'low' => ':information_source:',
            default => ':bell:',
        };
    }

    /**
     * Get the footer text for this notification.
     *
     * @return string
     */
    public function getFooter(): string
    {
        return 'ERP System';
    }

    /**
     * Check if this is an interval-based notification that should be deduplicated.
     *
     * @return bool
     */
    public function isIntervalBased(): bool
    {
        // By default, notifications with entity type and ID are interval-based
        return $this->getEntityType() !== null && $this->getEntityId() !== null;
    }

    /**
     * Get the category of this notification type.
     *
     * @return string
     */
    public function getCategory(): string
    {
        $type = $this->getNotificationType();
        $config = config("notifications.types.{$type}");

        return $config['category'] ?? 'system';
    }
}
