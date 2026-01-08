<?php

namespace App\Services\Notification\Messages;

use App\Models\Subscription;

class SubscriptionRenewalMessage extends NotificationMessage
{
    protected string $notificationType;

    public function __construct(
        protected Subscription $subscription,
        protected int $daysUntilRenewal,
        protected string $urgency = 'normal'
    ) {
        $this->notificationType = $this->determineNotificationType();
    }

    public function getTitle(): string
    {
        if ($this->daysUntilRenewal < 0) {
            return "Subscription Overdue: {$this->subscription->vendor_name}";
        }

        if ($this->daysUntilRenewal === 0) {
            return "Subscription Due Today: {$this->subscription->vendor_name}";
        }

        return "Subscription Renewal: {$this->subscription->vendor_name}";
    }

    public function getBody(): string
    {
        if ($this->daysUntilRenewal < 0) {
            $daysOverdue = abs($this->daysUntilRenewal);
            return "The subscription for {$this->subscription->vendor_name} is {$daysOverdue} day(s) overdue. Please review and renew immediately.";
        }

        if ($this->daysUntilRenewal === 0) {
            return "The subscription for {$this->subscription->vendor_name} is due for renewal TODAY.";
        }

        if ($this->daysUntilRenewal === 1) {
            return "The subscription for {$this->subscription->vendor_name} is due for renewal TOMORROW.";
        }

        return "The subscription for {$this->subscription->vendor_name} will renew in {$this->daysUntilRenewal} days.";
    }

    public function getPriority(): string
    {
        if ($this->daysUntilRenewal < 0) {
            return 'urgent';
        }

        return match ($this->urgency) {
            'overdue' => 'urgent',
            'urgent' => 'urgent',
            'warning' => 'high',
            default => 'normal',
        };
    }

    public function getNotificationType(): string
    {
        return $this->notificationType;
    }

    public function getEntityType(): ?string
    {
        return 'Subscription';
    }

    public function getEntityId(): ?int
    {
        return $this->subscription->id;
    }

    public function toArray(): array
    {
        return [
            'subscription_id' => $this->subscription->id,
            'vendor_name' => $this->subscription->vendor_name,
            'days_until_renewal' => $this->daysUntilRenewal,
            'urgency' => $this->urgency,
            'next_renewal_date' => $this->subscription->next_renewal_date?->format('Y-m-d'),
            'price' => $this->subscription->price,
            'currency' => $this->subscription->currency,
            'billing_cycle' => $this->subscription->billing_cycle,
            'status' => $this->subscription->status,
        ];
    }

    public function getFields(): array
    {
        $fields = [];

        $fields[] = [
            'title' => $this->daysUntilRenewal < 0 ? 'Days Overdue' : 'Days Until Renewal',
            'value' => abs($this->daysUntilRenewal) . ' day(s)',
            'short' => true,
        ];

        $fields[] = [
            'title' => 'Renewal Date',
            'value' => $this->subscription->next_renewal_date?->format('Y-m-d') ?? 'N/A',
            'short' => true,
        ];

        $currency = $this->subscription->currency ?? 'EUR';
        $currencySymbol = $currency === 'EUR' ? '€' : ($currency === 'USD' ? '$' : $currency . ' ');

        $fields[] = [
            'title' => 'Price',
            'value' => $currencySymbol . number_format($this->subscription->price, 2) . '/' . $this->subscription->billing_cycle_label,
            'short' => true,
        ];

        $fields[] = [
            'title' => 'Status',
            'value' => ucfirst($this->subscription->status),
            'short' => true,
        ];

        return $fields;
    }

    public function getUrl(): ?string
    {
        return url("/subscriptions/{$this->subscription->id}");
    }

    public function getColor(): string
    {
        if ($this->daysUntilRenewal < 0) {
            return '#d32f2f'; // Red - overdue
        }

        return match ($this->urgency) {
            'overdue' => '#d32f2f', // Red
            'urgent' => '#f44336', // Red
            'warning' => '#ff9800', // Orange
            default => '#4caf50', // Green
        };
    }

    public function getIcon(): string
    {
        if ($this->daysUntilRenewal < 0) {
            return ':rotating_light:';
        }

        return match ($this->urgency) {
            'overdue', 'urgent' => ':rotating_light:',
            'warning' => ':warning:',
            default => ':calendar:',
        };
    }

    protected function determineNotificationType(): string
    {
        if ($this->daysUntilRenewal < 0) {
            return 'subscription_overdue';
        }

        return match ($this->daysUntilRenewal) {
            30 => 'subscription_renewal_30d',
            14 => 'subscription_renewal_14d',
            7 => 'subscription_renewal_7d',
            3 => 'subscription_renewal_3d',
            1 => 'subscription_renewal_1d',
            0 => 'subscription_overdue',
            default => "subscription_renewal_{$this->daysUntilRenewal}d",
        };
    }
}
