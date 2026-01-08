<?php

namespace App\Notifications;

use App\Models\Offer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OfferVerificationCode extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Offer $offer,
        public string $code
    ) {}

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $organization = $this->offer->organization;
        $expiresIn = 30; // minutes

        return (new MailMessage)
            ->subject(__('Verification Code for Offer :number', ['number' => $this->offer->offer_number]))
            ->greeting(__('Hello!'))
            ->line(__('You have requested to accept the offer :number from :company.', [
                'number' => $this->offer->offer_number,
                'company' => $organization->name ?? config('app.name'),
            ]))
            ->line(__('Your verification code is:'))
            ->line("**{$this->code}**")
            ->line(__('This code will expire in :minutes minutes.', ['minutes' => $expiresIn]))
            ->line(__('If you did not request this code, please ignore this email.'))
            ->salutation(__('Best regards,') . "\n" . ($organization->name ?? config('app.name')));
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'offer_id' => $this->offer->id,
            'offer_number' => $this->offer->offer_number,
            'code' => $this->code,
        ];
    }
}
