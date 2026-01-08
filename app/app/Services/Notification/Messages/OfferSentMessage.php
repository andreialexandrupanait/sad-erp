<?php

namespace App\Services\Notification\Messages;

use App\Models\Offer;

class OfferSentMessage extends NotificationMessage
{
    public function __construct(
        protected Offer $offer
    ) {}

    public function getTitle(): string
    {
        return __('Offer Sent');
    }

    public function getBody(): string
    {
        return __('Offer :number has been sent to :client.', [
            'number' => $this->offer->offer_number,
            'client' => $this->offer->client?->display_name ?? 'Unknown',
        ]);
    }

    public function getPriority(): string
    {
        return 'normal';
    }

    public function getNotificationType(): string
    {
        return 'offer_sent';
    }

    public function getEntityType(): ?string
    {
        return 'Offer';
    }

    public function getEntityId(): ?int
    {
        return $this->offer->id;
    }

    public function getUrl(): ?string
    {
        return route('offers.show', $this->offer);
    }

    public function getFields(): array
    {
        return [
            [
                'title' => __('Offer Number'),
                'value' => $this->offer->offer_number,
                'short' => true,
            ],
            [
                'title' => __('Client'),
                'value' => $this->offer->client?->display_name ?? '-',
                'short' => true,
            ],
            [
                'title' => __('Total'),
                'value' => number_format($this->offer->total, 2) . ' ' . $this->offer->currency,
                'short' => true,
            ],
            [
                'title' => __('Valid Until'),
                'value' => $this->offer->valid_until?->format('d.m.Y') ?? '-',
                'short' => true,
            ],
        ];
    }

    public function toArray(): array
    {
        return [
            'offer_id' => $this->offer->id,
            'offer_number' => $this->offer->offer_number,
            'client_id' => $this->offer->client_id,
            'client_name' => $this->offer->client?->display_name,
            'total' => $this->offer->total,
            'currency' => $this->offer->currency,
        ];
    }

    public function isIntervalBased(): bool
    {
        return false;
    }
}
