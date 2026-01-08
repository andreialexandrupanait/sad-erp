<?php

namespace App\Mail;

use App\Models\Offer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OfferExpiredNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Offer $offer
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Offer Expired - :number', [
                'number' => $this->offer->offer_number,
            ]),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.offer-expired-notification',
            with: [
                'offer' => $this->offer,
                'organization' => $this->offer->organization,
            ],
        );
    }
}
