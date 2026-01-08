<?php

namespace App\Services\Notification\Messages;

use App\Models\FinancialRevenue;

class RevenueCreatedMessage extends NotificationMessage
{
    public function __construct(
        protected FinancialRevenue $revenue
    ) {}

    public function getTitle(): string
    {
        $clientName = $this->revenue->client?->display_name ?? 'Unknown Client';
        return "New Revenue: {$clientName}";
    }

    public function getBody(): string
    {
        $amount = $this->getFormattedAmount();
        $clientName = $this->revenue->client?->display_name ?? 'Unknown Client';
        $date = $this->revenue->occurred_at?->format('Y-m-d') ?? 'N/A';

        return "New revenue of {$amount} recorded from {$clientName} on {$date}.";
    }

    public function getPriority(): string
    {
        // Revenue notifications are generally informational
        return 'low';
    }

    public function getNotificationType(): string
    {
        return 'revenue_created';
    }

    public function getEntityType(): ?string
    {
        return 'FinancialRevenue';
    }

    public function getEntityId(): ?int
    {
        return $this->revenue->id;
    }

    public function toArray(): array
    {
        return [
            'revenue_id' => $this->revenue->id,
            'document_name' => $this->revenue->document_name,
            'amount' => $this->revenue->amount,
            'currency' => $this->revenue->currency,
            'occurred_at' => $this->revenue->occurred_at?->format('Y-m-d'),
            'client_id' => $this->revenue->client_id,
            'client_name' => $this->revenue->client?->display_name,
            'year' => $this->revenue->year,
            'month' => $this->revenue->month,
        ];
    }

    public function getFields(): array
    {
        $fields = [];

        if ($this->revenue->client) {
            $fields[] = [
                'title' => 'Client',
                'value' => $this->revenue->client->display_name,
                'short' => true,
            ];
        }

        $fields[] = [
            'title' => 'Amount',
            'value' => $this->getFormattedAmount(),
            'short' => true,
        ];

        $fields[] = [
            'title' => 'Date',
            'value' => $this->revenue->occurred_at?->format('Y-m-d') ?? 'N/A',
            'short' => true,
        ];

        if ($this->revenue->document_name) {
            $fields[] = [
                'title' => 'Document',
                'value' => $this->revenue->document_name,
                'short' => true,
            ];
        }

        return $fields;
    }

    public function getUrl(): ?string
    {
        return url("/financial/revenues/{$this->revenue->id}");
    }

    public function getColor(): string
    {
        return '#4caf50'; // Green - positive financial news
    }

    public function getIcon(): string
    {
        return ':moneybag:';
    }

    public function getFooter(): string
    {
        return 'ERP System - Financial Module';
    }

    /**
     * This is not an interval-based notification, so don't deduplicate.
     */
    public function isIntervalBased(): bool
    {
        return false;
    }

    protected function getFormattedAmount(): string
    {
        $currency = $this->revenue->currency ?? 'EUR';
        $symbol = match ($currency) {
            'EUR' => '€',
            'USD' => '$',
            'GBP' => '£',
            default => $currency . ' ',
        };

        return $symbol . number_format($this->revenue->amount, 2);
    }
}
