<?php

namespace App\Services\Notification\Formatters;

use App\Services\Notification\Messages\NotificationMessage;

/**
 * WhatsApp message formatter (placeholder for future implementation).
 *
 * This class provides formatting methods for WhatsApp messages.
 * WhatsApp uses plain text with limited formatting (bold, italic)
 * and supports message templates for business communications.
 */
class WhatsAppMessageFormatter
{
    /**
     * Format a notification message for WhatsApp.
     *
     * @param NotificationMessage $message
     * @return array WhatsApp API payload structure
     */
    public function format(NotificationMessage $message): array
    {
        return [
            'type' => 'text',
            'text' => [
                'body' => $this->formatBody($message),
            ],
        ];
    }

    /**
     * Format the message body.
     */
    protected function formatBody(NotificationMessage $message): string
    {
        $emoji = $this->getEmojiForPriority($message->getPriority());

        $body = sprintf(
            "%s *%s*\n\n%s",
            $emoji,
            $message->getTitle(),
            $message->getBody()
        );

        // Add fields
        $fields = $message->getFields();
        if (!empty($fields)) {
            $body .= "\n";
            foreach ($fields as $field) {
                $body .= sprintf("\n*%s:* %s", $field['title'] ?? '', $field['value'] ?? '');
            }
        }

        // Add URL if available
        if ($url = $message->getUrl()) {
            $body .= "\n\n🔗 View details: {$url}";
        }

        // Add footer
        $body .= "\n\n_" . $message->getFooter() . "_";

        return $body;
    }

    /**
     * Format a template message (for pre-approved templates).
     *
     * @param string $templateName
     * @param array $parameters
     * @return array
     */
    public function formatTemplate(string $templateName, array $parameters = []): array
    {
        return [
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => [
                    'code' => 'en',
                ],
                'components' => $this->buildTemplateComponents($parameters),
            ],
        ];
    }

    /**
     * Build template components from parameters.
     */
    protected function buildTemplateComponents(array $parameters): array
    {
        if (empty($parameters)) {
            return [];
        }

        $bodyParams = [];
        foreach ($parameters as $key => $value) {
            $bodyParams[] = [
                'type' => 'text',
                'text' => (string) $value,
            ];
        }

        return [
            [
                'type' => 'body',
                'parameters' => $bodyParams,
            ],
        ];
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

    /**
     * Format a test message.
     */
    public function formatTestMessage(): array
    {
        return [
            'type' => 'text',
            'text' => [
                'body' => "✅ *Test Notification*\n\nThis is a test message from your ERP notification system.\n\nIf you see this, WhatsApp integration is working correctly!\n\n_ERP System_",
            ],
        ];
    }
}
