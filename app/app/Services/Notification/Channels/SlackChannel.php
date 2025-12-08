<?php

namespace App\Services\Notification\Channels;

use App\Models\ApplicationSetting;
use App\Services\Notification\Formatters\SlackMessageFormatter;
use App\Services\Notification\Messages\NotificationMessage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SlackChannel implements NotificationChannelInterface
{
    protected SlackMessageFormatter $formatter;

    public function __construct(?SlackMessageFormatter $formatter = null)
    {
        $this->formatter = $formatter ?? new SlackMessageFormatter();
    }

    /**
     * {@inheritdoc}
     */
    public function send(NotificationMessage $message): bool
    {
        if (!$this->isConfigured()) {
            throw new \RuntimeException('Slack channel is not properly configured. Please set SLACK_WEBHOOK_URL.');
        }

        $webhookUrl = $this->getWebhookUrl();
        $payload = $this->formatter->format($message);

        return $this->sendPayload($webhookUrl, $payload);
    }

    /**
     * Send a test message to verify Slack configuration.
     */
    public function sendTest(): bool
    {
        if (!$this->isConfigured()) {
            throw new \RuntimeException('Slack channel is not properly configured. Please set SLACK_WEBHOOK_URL.');
        }

        $webhookUrl = $this->getWebhookUrl();
        $payload = $this->formatter->formatTestMessage();

        return $this->sendPayload($webhookUrl, $payload);
    }

    /**
     * Send a raw text message to Slack.
     */
    public function sendText(string $text): bool
    {
        if (!$this->isConfigured()) {
            throw new \RuntimeException('Slack channel is not properly configured. Please set SLACK_WEBHOOK_URL.');
        }

        $webhookUrl = $this->getWebhookUrl();
        $payload = $this->formatter->formatText($text);

        return $this->sendPayload($webhookUrl, $payload);
    }

    /**
     * Send payload to Slack webhook.
     */
    protected function sendPayload(string $webhookUrl, array $payload): bool
    {
        $timeout = config('notifications.channels.slack.timeout', 5);

        try {
            $response = Http::timeout($timeout)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->post($webhookUrl, $payload);

            if ($response->successful()) {
                return true;
            }

            Log::error('Slack webhook returned error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Slack webhook connection failed', [
                'error' => $e->getMessage(),
            ]);
            throw new \RuntimeException('Failed to connect to Slack: ' . $e->getMessage(), 0, $e);
        } catch (\Throwable $e) {
            Log::error('Slack webhook request failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isConfigured(): bool
    {
        $webhookUrl = $this->getWebhookUrl();

        if (empty($webhookUrl)) {
            return false;
        }

        // Basic URL validation
        if (!filter_var($webhookUrl, FILTER_VALIDATE_URL)) {
            return false;
        }

        // Check it's a Slack webhook URL
        if (!str_contains($webhookUrl, 'hooks.slack.com')) {
            Log::warning('Slack webhook URL does not appear to be a valid Slack webhook', [
                'url_host' => parse_url($webhookUrl, PHP_URL_HOST),
            ]);
            // Still allow non-Slack URLs for testing/mocking purposes
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'slack';
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled(): bool
    {
        // Check database setting first, then fall back to config
        return ApplicationSetting::get('slack_enabled', config('notifications.channels.slack.enabled', false));
    }

    /**
     * Get the Slack webhook URL from database or config.
     */
    protected function getWebhookUrl(): ?string
    {
        // Check database setting first, then fall back to config/env
        $webhookUrl = ApplicationSetting::get('slack_webhook_url');

        if (!empty($webhookUrl)) {
            return $webhookUrl;
        }

        return config('notifications.channels.slack.webhook_url');
    }

    /**
     * Get the Slack channel override from database.
     */
    protected function getChannel(): ?string
    {
        return ApplicationSetting::get('slack_channel');
    }
}
