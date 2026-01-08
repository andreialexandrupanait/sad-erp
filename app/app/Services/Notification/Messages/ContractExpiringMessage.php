<?php

namespace App\Services\Notification\Messages;

use App\Models\Contract;

class ContractExpiringMessage extends NotificationMessage
{
    public function __construct(
        protected Contract $contract,
        protected int $daysUntilExpiry
    ) {}

    public function getTitle(): string
    {
        if ($this->daysUntilExpiry <= 0) {
            return __('Contract Expired');
        }

        if ($this->daysUntilExpiry <= 7) {
            return __('Contract Expiring Soon!');
        }

        return __('Contract Expiring');
    }

    public function getBody(): string
    {
        if ($this->daysUntilExpiry <= 0) {
            return __('Contract :number with :client has expired.', [
                'number' => $this->contract->contract_number,
                'client' => $this->contract->client?->display_name ?? 'Unknown',
            ]);
        }

        return __('Contract :number with :client expires in :days days.', [
            'number' => $this->contract->contract_number,
            'client' => $this->contract->client?->display_name ?? 'Unknown',
            'days' => $this->daysUntilExpiry,
        ]);
    }

    public function getPriority(): string
    {
        if ($this->daysUntilExpiry <= 0) {
            return 'urgent';
        }

        if ($this->daysUntilExpiry <= 7) {
            return 'high';
        }

        if ($this->daysUntilExpiry <= 14) {
            return 'normal';
        }

        return 'low';
    }

    public function getNotificationType(): string
    {
        if ($this->daysUntilExpiry <= 7) {
            return 'contract_expiring_7d';
        }

        if ($this->daysUntilExpiry <= 14) {
            return 'contract_expiring_14d';
        }

        return 'contract_expiring_30d';
    }

    public function getEntityType(): ?string
    {
        return 'Contract';
    }

    public function getEntityId(): ?int
    {
        return $this->contract->id;
    }

    public function getUrl(): ?string
    {
        return route('contracts.show', $this->contract);
    }

    public function getFields(): array
    {
        return [
            [
                'title' => __('Contract Number'),
                'value' => $this->contract->contract_number,
                'short' => true,
            ],
            [
                'title' => __('Client'),
                'value' => $this->contract->client?->display_name ?? '-',
                'short' => true,
            ],
            [
                'title' => __('Value'),
                'value' => number_format($this->contract->total_value, 2) . ' ' . $this->contract->currency,
                'short' => true,
            ],
            [
                'title' => __('Expiry Date'),
                'value' => $this->contract->end_date?->format('d.m.Y') ?? '-',
                'short' => true,
            ],
            [
                'title' => __('Days Until Expiry'),
                'value' => $this->daysUntilExpiry <= 0 ? __('Expired') : $this->daysUntilExpiry . ' ' . __('days'),
                'short' => true,
            ],
            [
                'title' => __('Auto Renew'),
                'value' => $this->contract->auto_renew ? __('Yes') : __('No'),
                'short' => true,
            ],
        ];
    }

    public function toArray(): array
    {
        return [
            'contract_id' => $this->contract->id,
            'contract_number' => $this->contract->contract_number,
            'client_id' => $this->contract->client_id,
            'client_name' => $this->contract->client?->display_name,
            'total_value' => $this->contract->total_value,
            'currency' => $this->contract->currency,
            'end_date' => $this->contract->end_date?->toISOString(),
            'days_until_expiry' => $this->daysUntilExpiry,
            'auto_renew' => $this->contract->auto_renew,
        ];
    }

    public function isIntervalBased(): bool
    {
        return true;
    }
}
