<?php

namespace App\Services\Notification\Messages;

use App\Models\Offer;

class OfferAcceptedMessage extends NotificationMessage
{
    public function __construct(
        protected Offer $offer
    ) {}

    public function getTitle(): string
    {
        return __('Offer Accepted!');
    }

    public function getBody(): string
    {
        return __('Great news! Offer :number has been accepted by :client.', [
            'number' => $this->offer->offer_number,
            'client' => $this->offer->client?->display_name ?? 'Unknown',
        ]);
    }

    public function getPriority(): string
    {
        return 'high';
    }

    public function getNotificationType(): string
    {
        return 'offer_accepted';
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

    public function getColor(): string
    {
        return '#4caf50'; // Green for success
    }

    public function getIcon(): string
    {
        return ':white_check_mark:';
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
                'title' => __('Accepted At'),
                'value' => $this->offer->accepted_at?->format('d.m.Y H:i') ?? now()->format('d.m.Y H:i'),
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
            'accepted_at' => $this->offer->accepted_at?->toISOString(),
        ];
    }

    public function isIntervalBased(): bool
    {
        return false;
    }
}
