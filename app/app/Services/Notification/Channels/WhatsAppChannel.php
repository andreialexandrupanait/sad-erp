<?php

namespace App\Services\Notification\Channels;

use App\Services\Notification\Messages\NotificationMessage;

/**
 * WhatsApp notification channel (placeholder for future implementation).
 *
 * This class provides a stub implementation of the NotificationChannelInterface
 * for WhatsApp. It's disabled by default and will throw NotImplementedException
 * if used before full implementation.
 *
 * Future implementation notes:
 * - Consider using Twilio, Meta, or Vonage WhatsApp Business API
 * - Will need to handle message templates (pre-approved by Meta)
 * - Phone number management in E.164 format
 * - Rate limiting (more restrictive than Slack)
 * - Two-way communication support (optional)
 */
class WhatsAppChannel implements NotificationChannelInterface
{
    /**
     * {@inheritdoc}
     */
    public function send(NotificationMessage $message): bool
    {
        if (!$this->isEnabled()) {
            throw new \RuntimeException('WhatsApp notifications are not enabled.');
        }

        if (!$this->isConfigured()) {
            throw new \RuntimeException('WhatsApp channel is not properly configured.');
        }

        // TODO: Implement WhatsApp API integration
        // Example implementation:
        //
        // $apiUrl = config('notifications.channels.whatsapp.api_url');
        // $apiToken = config('notifications.channels.whatsapp.api_token');
        //
        // $payload = [
        //     'to' => $this->getRecipientNumber(),
        //     'type' => 'text',
        //     'text' => [
        //         'body' => $this->formatMessage($message),
        //     ],
        // ];
        //
        // $response = Http::withToken($apiToken)
        //     ->post($apiUrl, $payload);
        //
        // return $response->successful();

        throw new \RuntimeException(
            'WhatsApp channel is not yet implemented. ' .
            'This is a placeholder for future development. ' .
            'Please use Slack channel for notifications.'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function isConfigured(): bool
    {
        $apiUrl = config('notifications.channels.whatsapp.api_url');
        $apiToken = config('notifications.channels.whatsapp.api_token');

        return !empty($apiUrl) && !empty($apiToken);
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'whatsapp';
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled(): bool
    {
        return config('notifications.channels.whatsapp.enabled', false);
    }

    /**
     * Format a message for WhatsApp (text-based).
     *
     * @param NotificationMessage $message
     * @return string
     */
    protected function formatMessage(NotificationMessage $message): string
    {
        $emoji = $this->getEmojiForPriority($message->getPriority());

        $text = sprintf(
            "%s *%s*\n\n%s",
            $emoji,
            $message->getTitle(),
            $message->getBody()
        );

        // Add fields
        foreach ($message->getFields() as $field) {
            $text .= sprintf("\n*%s:* %s", $field['title'], $field['value']);
        }

        // Add URL if available
        if ($url = $message->getUrl()) {
            $text .= "\n\nView details: {$url}";
        }

        return $text;
    }

    /**
     * Get emoji for priority level.
     */
    protected function getEmojiForPriority(string $priority): string
    {
        return match ($priority) {
            'urgent' => '🚨',
            'high' => '⚠️',
            'normal' => '🔔',
            'low' => 'ℹ️',
            default => '🔔',
        };
    }
}
