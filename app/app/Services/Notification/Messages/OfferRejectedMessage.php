<?php

namespace App\Services\Notification\Messages;

use App\Models\Offer;

class OfferRejectedMessage extends NotificationMessage
{
    public function __construct(
        protected Offer $offer
    ) {}

    public function getTitle(): string
    {
        return __('Offer Rejected');
    }

    public function getBody(): string
    {
        $message = __('Offer :number has been rejected by :client.', [
            'number' => $this->offer->offer_number,
            'client' => $this->offer->client?->display_name ?? 'Unknown',
        ]);

        if ($this->offer->rejection_reason) {
            $message .= ' ' . __('Reason: :reason', ['reason' => $this->offer->rejection_reason]);
        }

        return $message;
    }

    public function getPriority(): string
    {
        return 'normal';
    }

    public function getNotificationType(): string
    {
        return 'offer_rejected';
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
        return '#f44336'; // Red for rejection
    }

    public function getIcon(): string
    {
        return ':x:';
    }

    public function getFields(): array
    {
        $fields = [
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
                'title' => __('Rejected At'),
                'value' => $this->offer->rejected_at?->format('d.m.Y H:i') ?? now()->format('d.m.Y H:i'),
                'short' => true,
            ],
        ];

        if ($this->offer->rejection_reason) {
            $fields[] = [
                'title' => __('Rejection Reason'),
                'value' => $this->offer->rejection_reason,
                'short' => false,
            ];
        }

        return $fields;
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
            'rejected_at' => $this->offer->rejected_at?->toISOString(),
            'rejection_reason' => $this->offer->rejection_reason,
        ];
    }

    public function isIntervalBased(): bool
    {
        return false;
    }
}
