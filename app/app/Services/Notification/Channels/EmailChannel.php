<?php

namespace App\Services\Notification\Channels;

use App\Mail\NotificationMail;
use App\Models\ApplicationSetting;
use App\Services\Notification\Messages\NotificationMessage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailChannel implements NotificationChannelInterface
{
    /**
     * Configure SMTP from database settings if custom SMTP is enabled.
     */
    protected function configureSmtp(): void
    {
        $smtpEnabled = ApplicationSetting::get('smtp_enabled', false);

        if ($smtpEnabled) {
            $smtpHost = ApplicationSetting::get('smtp_host');
            $smtpPort = ApplicationSetting::get('smtp_port', 587);
            $smtpUsername = ApplicationSetting::get('smtp_username');
            $smtpPassword = ApplicationSetting::get('smtp_password');
            $smtpEncryption = ApplicationSetting::get('smtp_encryption', 'tls');
            $fromEmail = ApplicationSetting::get('smtp_from_email');
            $fromName = ApplicationSetting::get('smtp_from_name', config('app.name'));

            // Decrypt password if encrypted
            if ($smtpPassword) {
                try {
                    $smtpPassword = decrypt($smtpPassword);
                } catch (\Exception $e) {
                    // Password might not be encrypted
                }
            }

            // Configure SMTP on the fly
            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.host' => $smtpHost,
                'mail.mailers.smtp.port' => (int) $smtpPort,
                'mail.mailers.smtp.username' => $smtpUsername,
                'mail.mailers.smtp.password' => $smtpPassword,
                'mail.mailers.smtp.encryption' => $smtpEncryption === 'none' ? null : $smtpEncryption,
                'mail.from.address' => $fromEmail ?: $smtpUsername,
                'mail.from.name' => $fromName,
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function send(NotificationMessage $message): bool
    {
        if (!$this->isConfigured()) {
            throw new \RuntimeException('Email channel is not properly configured. Please configure mail settings.');
        }

        $recipients = $this->getRecipients();

        if (empty($recipients)) {
            Log::warning('Email notification skipped: no recipients configured');
            return false;
        }

        try {
            // Configure SMTP from database if needed
            $this->configureSmtp();

            Mail::to($recipients)->send(new NotificationMail($message));
            return true;
        } catch (\Throwable $e) {
            Log::error('Failed to send email notification', [
                'error' => $e->getMessage(),
                'recipients' => $recipients,
                'notification_type' => $message->getNotificationType(),
            ]);
            throw $e;
        }
    }

    /**
     * Send a test email to verify configuration.
     */
    public function sendTest(?string $recipientEmail = null): bool
    {
        if (!$this->isConfigured()) {
            throw new \RuntimeException('Email channel is not properly configured. Please configure mail settings.');
        }

        $recipient = $recipientEmail ?? $this->getAdminEmail();

        if (empty($recipient)) {
            throw new \RuntimeException('No recipient email specified for test.');
        }

        try {
            // Configure SMTP from database if needed
            $this->configureSmtp();

            Mail::to($recipient)->send(new NotificationMail(null, true));
            return true;
        } catch (\Throwable $e) {
            Log::error('Failed to send test email', [
                'error' => $e->getMessage(),
                'recipient' => $recipient,
            ]);
            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isConfigured(): bool
    {
        // Check if custom SMTP is enabled in database
        $smtpEnabled = ApplicationSetting::get('smtp_enabled', false);

        if ($smtpEnabled) {
            $smtpHost = ApplicationSetting::get('smtp_host');
            $smtpUsername = ApplicationSetting::get('smtp_username');
            return !empty($smtpHost) && !empty($smtpUsername);
        }

        // Check default mail config
        $mailer = config('mail.default');

        if ($mailer === 'log' || $mailer === 'array') {
            // These are valid for testing but not production - return false for real sending
            return false;
        }

        // For SMTP, check required settings
        if ($mailer === 'smtp') {
            $host = config('mail.mailers.smtp.host');
            return !empty($host) && $host !== 'mailpit' && $host !== '127.0.0.1';
        }

        return !empty($mailer);
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'email';
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled(): bool
    {
        // Check if notifications are globally enabled
        return ApplicationSetting::get('notifications_enabled', config('notifications.channels.email.enabled', false));
    }

    /**
     * Get the admin email address for notifications.
     */
    public function getAdminEmail(): ?string
    {
        // Check both possible setting names
        return ApplicationSetting::get('notification_email_primary')
            ?? ApplicationSetting::get('email_notifications_admin')
            ?? config('notifications.channels.email.admin_email');
    }

    /**
     * Get all notification recipients.
     *
     * @return array
     */
    protected function getRecipients(): array
    {
        $recipients = [];

        // Add primary email
        $primaryEmail = ApplicationSetting::get('notification_email_primary');
        if (!empty($primaryEmail)) {
            $recipients[] = trim($primaryEmail);
        }

        // Add CC emails if configured
        $ccEmails = ApplicationSetting::get('notification_email_cc');
        if (!empty($ccEmails)) {
            $emails = array_map('trim', explode(',', $ccEmails));
            $recipients = array_merge($recipients, $emails);
        }

        // Fallback to admin email setting
        if (empty($recipients)) {
            $adminEmail = ApplicationSetting::get('email_notifications_admin');
            if (!empty($adminEmail)) {
                $emails = array_map('trim', explode(',', $adminEmail));
                $recipients = array_merge($recipients, $emails);
            }
        }

        // Filter valid emails
        $recipients = array_filter($recipients, function ($email) {
            return filter_var($email, FILTER_VALIDATE_EMAIL);
        });

        return array_unique($recipients);
    }

    /**
     * Check if a specific notification type is enabled for email.
     */
    public function isTypeEnabled(string $notificationType): bool
    {
        $settingKey = 'email_notify_' . str_replace('.', '_', $notificationType);
        return ApplicationSetting::get($settingKey, true);
    }
}
