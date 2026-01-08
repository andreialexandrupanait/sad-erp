<?php

namespace App\Services\Notification\Formatters;

use App\Services\Notification\Messages\NotificationMessage;

class SlackMessageFormatter
{
    /**
     * Format a notification message for Slack.
     *
     * @param NotificationMessage $message
     * @return array Slack webhook payload
     */
    public function format(NotificationMessage $message): array
    {
        $attachment = [
            'color' => $message->getColor(),
            'fallback' => $message->getTitle() . ' - ' . $message->getBody(),
            'pretext' => $this->formatPretext($message),
            'title' => $message->getTitle(),
            'text' => $message->getBody(),
            'fields' => $this->formatFields($message->getFields()),
            'footer' => $message->getFooter(),
            'footer_icon' => $this->getFooterIcon(),
            'ts' => time(),
        ];

        // Add title link if URL is available
        if ($url = $message->getUrl()) {
            $attachment['title_link'] = $url;
        }

        return [
            'username' => config('notifications.channels.slack.username', 'ERP Notifications'),
            'icon_emoji' => config('notifications.channels.slack.icon', ':bell:'),
            'attachments' => [$attachment],
        ];
    }

    /**
     * Format a simple text message for Slack.
     *
     * @param string $text
     * @return array
     */
    public function formatText(string $text): array
    {
        return [
            'username' => config('notifications.channels.slack.username', 'ERP Notifications'),
            'icon_emoji' => config('notifications.channels.slack.icon', ':bell:'),
            'text' => $text,
        ];
    }

    /**
     * Format the pretext with icon based on priority.
     */
    protected function formatPretext(NotificationMessage $message): string
    {
        $icon = $message->getIcon();
        $category = $this->getCategoryLabel($message->getCategory());

        return "{$icon} {$category}";
    }

    /**
     * Format fields for Slack attachment.
     *
     * @param array $fields
     * @return array
     */
    protected function formatFields(array $fields): array
    {
        $formatted = [];

        foreach ($fields as $field) {
            $formatted[] = [
                'title' => $field['title'] ?? '',
                'value' => $field['value'] ?? '',
                'short' => $field['short'] ?? true,
            ];
        }

        return $formatted;
    }

    /**
     * Get human-readable category label.
     */
    protected function getCategoryLabel(string $category): string
    {
        $labels = [
            'domain' => 'Domain Alert',
            'subscription' => 'Subscription Alert',
            'financial' => 'Financial Update',
            'client' => 'Client Update',
            'system' => 'System Alert',
        ];

        return $labels[$category] ?? 'Notification';
    }

    /**
     * Get footer icon URL.
     */
    protected function getFooterIcon(): string
    {
        // Return empty string to use default Slack footer icon
        // You can customize this to your app's favicon URL
        return '';
    }

    /**
     * Format a test message.
     */
    public function formatTestMessage(): array
    {
        return [
            'username' => config('notifications.channels.slack.username', 'ERP Notifications'),
            'icon_emoji' => config('notifications.channels.slack.icon', ':bell:'),
            'attachments' => [
                [
                    'color' => '#4caf50',
                    'pretext' => ':white_check_mark: Test Notification',
                    'title' => 'Notification System Test',
                    'text' => 'This is a test message from your ERP notification system. If you see this, Slack integration is working correctly!',
                    'fields' => [
                        [
                            'title' => 'Environment',
                            'value' => config('app.env', 'production'),
                            'short' => true,
                        ],
                        [
                            'title' => 'Timestamp',
                            'value' => now()->format('Y-m-d H:i:s'),
                            'short' => true,
                        ],
                    ],
                    'footer' => 'ERP Notification System',
                    'ts' => time(),
                ],
            ],
        ];
    }
}
