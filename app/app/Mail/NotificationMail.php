<?php

namespace App\Mail;

use App\Services\Notification\Messages\NotificationMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public ?NotificationMessage $notificationMessage;
    public bool $isTest;

    /**
     * Create a new message instance.
     */
    public function __construct(?NotificationMessage $message = null, bool $isTest = false)
    {
        $this->notificationMessage = $message;
        $this->isTest = $isTest;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->isTest
            ? '[TEST] ' . config('app.name') . ' - Notification System Test'
            : $this->formatSubject();

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.notification',
            with: [
                'notification' => $this->notificationMessage,
                'isTest' => $this->isTest,
                'appName' => config('app.name'),
                'appUrl' => config('app.url'),
            ],
        );
    }

    /**
     * Format the email subject from the notification message.
     */
    protected function formatSubject(): string
    {
        if (!$this->notificationMessage) {
            return config('app.name') . ' - Notification';
        }

        $prefix = $this->getPriorityPrefix();
        $category = $this->getCategoryLabel();
        $title = $this->notificationMessage->getTitle();

        return "{$prefix}[{$category}] {$title}";
    }

    /**
     * Get subject prefix based on priority.
     */
    protected function getPriorityPrefix(): string
    {
        if (!$this->notificationMessage) {
            return '';
        }

        return match ($this->notificationMessage->getPriority()) {
            'urgent' => __('notifications.email.subject_prefix_urgent'),
            'high' => '',
            default => '',
        };
    }

    /**
     * Get human-readable category label.
     */
    protected function getCategoryLabel(): string
    {
        if (!$this->notificationMessage) {
            return __('notifications.email.category_alert');
        }

        $category = $this->notificationMessage->getCategory();
        $key = 'notifications.email.category_' . $category;

        return __($key);
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
