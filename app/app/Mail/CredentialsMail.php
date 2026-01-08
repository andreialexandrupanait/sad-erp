<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class CredentialsMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $siteName;
    public Collection $credentials;
    public ?string $customMessage;
    public string $emailSubject;

    /**
     * Create a new message instance.
     */
    public function __construct(
        string $siteName,
        Collection $credentials,
        ?string $customMessage = null,
        ?string $subject = null
    ) {
        $this->siteName = $siteName;
        $this->credentials = $credentials;
        $this->customMessage = $customMessage;
        $this->emailSubject = $subject ?? __('Access Credentials for :site', ['site' => $siteName]);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->emailSubject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.credentials',
            with: [
                'siteName' => $this->siteName,
                'credentials' => $this->credentials,
                'customMessage' => $this->customMessage,
                'appName' => config('app.name'),
                'appUrl' => config('app.url'),
            ],
        );
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
