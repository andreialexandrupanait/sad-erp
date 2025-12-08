<?php

namespace App\Console\Commands;

use App\Services\Notification\Channels\SlackChannel;
use App\Services\Notification\Channels\WhatsAppChannel;
use App\Services\Notification\NotificationService;
use Illuminate\Console\Command;

class TestNotificationChannel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:test
                            {channel=slack : The channel to test (slack, whatsapp)}
                            {--message= : Custom message to send}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test notification to verify channel configuration';

    /**
     * Execute the console command.
     */
    public function handle(NotificationService $service): int
    {
        $channelName = $this->argument('channel');
        $customMessage = $this->option('message');

        $this->info("Testing notification channel: {$channelName}");
        $this->newLine();

        // Check if notifications are enabled globally
        if (!config('notifications.enabled', true)) {
            $this->error('Notifications are globally disabled. Set NOTIFICATIONS_ENABLED=true in .env');
            return Command::FAILURE;
        }

        try {
            $result = match ($channelName) {
                'slack' => $this->testSlack($customMessage),
                'whatsapp' => $this->testWhatsApp($customMessage),
                default => $this->invalidChannel($channelName),
            };

            return $result ? Command::SUCCESS : Command::FAILURE;
        } catch (\Throwable $e) {
            $this->error("Test failed: {$e->getMessage()}");
            $this->newLine();
            $this->line("Stack trace:");
            $this->line($e->getTraceAsString());
            return Command::FAILURE;
        }
    }

    /**
     * Test Slack channel.
     */
    protected function testSlack(?string $customMessage): bool
    {
        $channel = new SlackChannel();

        // Check if enabled
        if (!$channel->isEnabled()) {
            $this->error('Slack notifications are disabled. Set SLACK_NOTIFICATIONS_ENABLED=true in .env');
            return false;
        }

        // Check configuration
        if (!$channel->isConfigured()) {
            $this->error('Slack is not properly configured.');
            $this->newLine();
            $this->warn('Please ensure the following environment variables are set:');
            $this->line('  SLACK_WEBHOOK_URL=https://hooks.slack.com/services/YOUR/WEBHOOK/URL');
            return false;
        }

        $this->info('Configuration OK. Sending test message...');
        $this->newLine();

        if ($customMessage) {
            $success = $channel->sendText($customMessage);
        } else {
            $success = $channel->sendTest();
        }

        if ($success) {
            $this->info('✓ Test notification sent successfully to Slack!');
            $this->newLine();
            $this->line('Check your Slack channel for the test message.');
            return true;
        }

        $this->error('✗ Failed to send test notification.');
        return false;
    }

    /**
     * Test WhatsApp channel.
     */
    protected function testWhatsApp(?string $customMessage): bool
    {
        $channel = new WhatsAppChannel();

        // Check if enabled
        if (!$channel->isEnabled()) {
            $this->warn('WhatsApp notifications are disabled (this is expected - WhatsApp is not yet implemented).');
            $this->newLine();
            $this->line('WhatsApp integration is a placeholder for future development.');
            $this->line('Please use the Slack channel for notifications.');
            return false;
        }

        // Check configuration
        if (!$channel->isConfigured()) {
            $this->error('WhatsApp is not properly configured.');
            $this->newLine();
            $this->warn('Please ensure the following environment variables are set:');
            $this->line('  WHATSAPP_API_URL=your_api_url');
            $this->line('  WHATSAPP_API_TOKEN=your_api_token');
            return false;
        }

        $this->warn('WhatsApp channel is not yet fully implemented.');
        $this->line('This is a placeholder for future development.');
        return false;
    }

    /**
     * Handle invalid channel name.
     */
    protected function invalidChannel(string $channelName): bool
    {
        $this->error("Invalid channel: {$channelName}");
        $this->newLine();
        $this->info('Available channels:');
        $this->line('  slack     - Slack webhook notifications');
        $this->line('  whatsapp  - WhatsApp notifications (placeholder)');
        return false;
    }
}
