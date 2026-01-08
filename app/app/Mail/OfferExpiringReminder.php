<?php

namespace App\Mail;

use App\Models\Offer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OfferExpiringReminder extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Offer $offer,
        public int $daysRemaining
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Reminder: Offer :number expires in :days days', [
                'number' => $this->offer->offer_number,
                'days' => $this->daysRemaining,
            ]),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.offer-expiring-reminder',
            with: [
                'offer' => $this->offer,
                'daysRemaining' => $this->daysRemaining,
                'organization' => $this->offer->organization,
                'publicUrl' => $this->offer->public_url,
            ],
        );
    }
}
