<?php

namespace App\Events;

use App\Models\Offer;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OfferAccepted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Offer $offer
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("organization." . $this->offer->organization_id),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            "offer_id" => $this->offer->id,
            "offer_number" => $this->offer->offer_number,
            "title" => $this->offer->title,
            "client_name" => $this->offer->client?->display_name ?? $this->offer->temp_client_name,
            "total" => number_format($this->offer->total, 2) . " " . $this->offer->currency,
            "accepted_at" => $this->offer->accepted_at?->format("d.m.Y H:i"),
            "message" => __("Offer :number has been accepted!", ["number" => $this->offer->offer_number]),
        ];
    }

    public function broadcastAs(): string
    {
        return "offer.accepted";
    }
}