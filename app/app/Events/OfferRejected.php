<?php

namespace App\Events;

use App\Models\Offer;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OfferRejected implements ShouldBroadcast
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
            "rejected_at" => $this->offer->rejected_at?->format("d.m.Y H:i"),
            "rejection_reason" => $this->offer->rejection_reason,
            "message" => __("Offer :number has been rejected.", ["number" => $this->offer->offer_number]),
        ];
    }

    public function broadcastAs(): string
    {
        return "offer.rejected";
    }
}