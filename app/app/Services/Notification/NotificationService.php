<?php

namespace App\Services\Notification;

use App\Models\NotificationLog;
use App\Models\User;
use App\Services\Notification\Channels\NotificationChannelInterface;
use App\Services\Notification\Messages\NotificationMessage;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * @var array<string, NotificationChannelInterface>
     */
    protected array $channels = [];

    /**
     * Register a notification channel.
     */
    public function registerChannel(NotificationChannelInterface $channel): self
    {
        $this->channels[$channel->getName()] = $channel;
        return $this;
    }

    /**
     * Get a registered channel by name.
     */
    public function getChannel(string $name): ?NotificationChannelInterface
    {
        return $this->channels[$name] ?? null;
    }

    /**
     * Get all registered channels.
     *
     * @return array<string, NotificationChannelInterface>
     */
    public function getChannels(): array
    {
        return $this->channels;
    }

    /**
     * Send a notification through specified channels (or all enabled channels).
     *
     * @param NotificationMessage $message The message to send
     * @param array|null $channels Channel names to use (null = all enabled)
     * @param int|null $organizationId Organization context (null = from auth)
     */
    public function send(
        NotificationMessage $message,
        ?array $channels = null,
        ?int $organizationId = null
    ): void {
        // Check if notifications are globally enabled
        if (!config('notifications.enabled', true)) {
            return;
        }

        $organizationId = $organizationId ?? $this->getOrganizationId();
        $channels = $channels ?? $this->getEnabledChannelNames();

        foreach ($channels as $channelName) {
            $channel = $this->getChannel($channelName);

            if (!$channel) {
                Log::warning("Notification channel not found", ['channel' => $channelName]);
                continue;
            }

            if (!$channel->isEnabled() || !$channel->isConfigured()) {
                continue;
            }

            // Check if this notification was already sent (for interval-based)
            if ($this->wasAlreadySent($message, $channelName)) {
                $this->logNotification($message, $channelName, 'skipped', 'Already sent', $organizationId);
                continue;
            }

            try {
                $success = $channel->send($message);
                $this->logNotification(
                    $message,
                    $channelName,
                    $success ? 'sent' : 'failed',
                    null,
                    $organizationId
                );
            } catch (\Throwable $e) {
                $this->logNotification($message, $channelName, 'failed', $e->getMessage(), $organizationId);
                Log::error("Notification failed", [
                    'channel' => $channelName,
                    'message_type' => $message->getNotificationType(),
                    'message_title' => $message->getTitle(),
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }
    }

    /**
     * Send a notification to a specific channel.
     *
     * @param NotificationMessage $message
     * @param string $channelName
     * @return bool
     */
    public function sendToChannel(NotificationMessage $message, string $channelName): bool
    {
        $channel = $this->getChannel($channelName);

        if (!$channel) {
            throw new \InvalidArgumentException("Channel '{$channelName}' not found");
        }

        if (!$channel->isConfigured()) {
            throw new \RuntimeException("Channel '{$channelName}' is not properly configured");
        }

        return $channel->send($message);
    }

    /**
     * Check if a notification was already sent for this entity/type combination.
     */
    protected function wasAlreadySent(NotificationMessage $message, string $channel): bool
    {
        // Only check for interval-based notifications
        if (!$message->isIntervalBased()) {
            return false;
        }

        $entityType = $message->getEntityType();
        $entityId = $message->getEntityId();
        $notificationType = $message->getNotificationType();

        if (!$entityType || !$entityId) {
            return false;
        }

        return NotificationLog::wasAlreadySent($entityType, $entityId, $notificationType, $channel);
    }

    /**
     * Log a notification attempt.
     */
    protected function logNotification(
        NotificationMessage $message,
        string $channel,
        string $status,
        ?string $errorMessage = null,
        ?int $organizationId = null
    ): void {
        try {
            NotificationLog::create([
                'organization_id' => $organizationId ?? $this->getOrganizationId() ?? 1,
                'user_id' => auth()->id(),
                'notification_type' => $message->getNotificationType(),
                'channel' => $channel,
                'entity_type' => $message->getEntityType(),
                'entity_id' => $message->getEntityId(),
                'payload' => $message->toArray(),
                'status' => $status,
                'error_message' => $errorMessage,
                'sent_at' => $status === 'sent' ? now() : null,
            ]);
        } catch (\Throwable $e) {
            // Don't let logging failures break the notification system
            Log::error("Failed to log notification", [
                'error' => $e->getMessage(),
                'notification_type' => $message->getNotificationType(),
            ]);
        }
    }

    /**
     * Get names of all enabled channels.
     *
     * @return array<string>
     */
    protected function getEnabledChannelNames(): array
    {
        $enabled = [];

        foreach ($this->channels as $name => $channel) {
            if ($channel->isEnabled() && $channel->isConfigured()) {
                $enabled[] = $name;
            }
        }

        return $enabled;
    }

    /**
     * Get the current organization ID from authenticated user.
     */
    protected function getOrganizationId(): ?int
    {
        if (auth()->check() && auth()->user()->organization_id) {
            return auth()->user()->organization_id;
        }

        return null;
    }

    /**
     * Check if a specific notification type is enabled for a user.
     */
    public function isNotificationTypeEnabledForUser(User $user, string $notificationType): bool
    {
        // Get user's notification preferences
        $settings = $user->settings ?? [];
        $preferences = $settings['notifications']['types'] ?? [];

        // Check if user has explicitly set a preference
        if (isset($preferences[$notificationType])) {
            return (bool) $preferences[$notificationType];
        }

        // Fall back to default from config
        $typeConfig = config("notifications.types.{$notificationType}");

        return $typeConfig['default_enabled'] ?? true;
    }

    /**
     * Check if a channel is enabled for a user.
     */
    public function isChannelEnabledForUser(User $user, string $channelName): bool
    {
        $settings = $user->settings ?? [];
        $channels = $settings['notifications']['channels'] ?? [];

        // Check if user has explicitly set a preference
        if (isset($channels[$channelName])) {
            return (bool) $channels[$channelName];
        }

        // Default to enabled
        return true;
    }

    /**
     * Get notification statistics for an organization.
     */
    public function getStatistics(int $organizationId, ?int $days = 30): array
    {
        $since = now()->subDays($days);

        $stats = NotificationLog::where('organization_id', $organizationId)
            ->where('created_at', '>=', $since)
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed,
                SUM(CASE WHEN status = 'skipped' THEN 1 ELSE 0 END) as skipped
            ")
            ->first();

        $byChannel = NotificationLog::where('organization_id', $organizationId)
            ->where('created_at', '>=', $since)
            ->where('status', 'sent')
            ->selectRaw('channel, COUNT(*) as count')
            ->groupBy('channel')
            ->pluck('count', 'channel')
            ->toArray();

        $byType = NotificationLog::where('organization_id', $organizationId)
            ->where('created_at', '>=', $since)
            ->where('status', 'sent')
            ->selectRaw('notification_type, COUNT(*) as count')
            ->groupBy('notification_type')
            ->pluck('count', 'notification_type')
            ->toArray();

        return [
            'total' => (int) ($stats->total ?? 0),
            'sent' => (int) ($stats->sent ?? 0),
            'failed' => (int) ($stats->failed ?? 0),
            'skipped' => (int) ($stats->skipped ?? 0),
            'by_channel' => $byChannel,
            'by_type' => $byType,
            'period_days' => $days,
        ];
    }
}
