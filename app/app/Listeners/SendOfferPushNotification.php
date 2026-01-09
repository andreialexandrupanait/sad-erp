<?php

namespace App\Listeners;

use App\Events\OfferAccepted;
use App\Events\OfferRejected;
use App\Services\WebPushService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendOfferPushNotification implements ShouldQueue
{
    protected WebPushService $webPushService;

    public function __construct(WebPushService $webPushService)
    {
        $this->webPushService = $webPushService;
    }

    /**
     * Handle offer accepted event.
     */
    public function handleOfferAccepted(OfferAccepted $event): void
    {
        $offer = $event->offer;
        
        $payload = [
            'title' => __('Offer Accepted!'),
            'body' => __(':client has accepted offer :number', [
                'client' => $offer->client?->display_name ?? $offer->temp_client_name,
                'number' => $offer->offer_number,
            ]),
            'icon' => '/images/icons/offer-accepted.png',
            'badge' => '/images/icons/badge.png',
            'data' => [
                'type' => 'offer_accepted',
                'offer_id' => $offer->id,
                'url' => route('offers.show', $offer->id),
            ],
            'tag' => 'offer-' . $offer->id,
            'requireInteraction' => true,
        ];

        $this->webPushService->sendToOrganization($offer->organization_id, $payload);
    }

    /**
     * Handle offer rejected event.
     */
    public function handleOfferRejected(OfferRejected $event): void
    {
        $offer = $event->offer;
        
        $payload = [
            'title' => __('Offer Rejected'),
            'body' => __(':client has rejected offer :number', [
                'client' => $offer->client?->display_name ?? $offer->temp_client_name,
                'number' => $offer->offer_number,
            ]),
            'icon' => '/images/icons/offer-rejected.png',
            'badge' => '/images/icons/badge.png',
            'data' => [
                'type' => 'offer_rejected',
                'offer_id' => $offer->id,
                'url' => route('offers.show', $offer->id),
            ],
            'tag' => 'offer-' . $offer->id,
            'requireInteraction' => false,
        ];

        $this->webPushService->sendToOrganization($offer->organization_id, $payload);
    }

    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe($events): array
    {
        return [
            OfferAccepted::class => 'handleOfferAccepted',
            OfferRejected::class => 'handleOfferRejected',
        ];
    }
}
