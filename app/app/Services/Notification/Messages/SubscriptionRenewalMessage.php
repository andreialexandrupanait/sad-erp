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
            return __('notifications.subscription.overdue_title', ['vendor' => $this->subscription->vendor_name]);
        }

        if ($this->daysUntilRenewal === 0) {
            return __('notifications.subscription.due_today_title', ['vendor' => $this->subscription->vendor_name]);
        }

        return __('notifications.subscription.renewal_title', ['vendor' => $this->subscription->vendor_name]);
    }

    public function getBody(): string
    {
        $vendor = $this->subscription->vendor_name;

        if ($this->daysUntilRenewal < 0) {
            $daysOverdue = abs($this->daysUntilRenewal);
            $daysText = trans_choice('notifications.subscription.day', $daysOverdue);
            return __('notifications.subscription.overdue_body', [
                'vendor' => $vendor,
                'days' => $daysOverdue . ' ' . $daysText,
            ]);
        }

        if ($this->daysUntilRenewal === 0) {
            return __('notifications.subscription.due_today_body', ['vendor' => $vendor]);
        }

        if ($this->daysUntilRenewal === 1) {
            return __('notifications.subscription.due_tomorrow_body', ['vendor' => $vendor]);
        }

        $daysText = trans_choice('notifications.subscription.day', $this->daysUntilRenewal);
        return __('notifications.subscription.renewal_body', [
            'vendor' => $vendor,
            'days' => $this->daysUntilRenewal . ' ' . $daysText,
        ]);
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
        $days = abs($this->daysUntilRenewal);
        $daysText = trans_choice('notifications.subscription.day', $days);

        $fields[] = [
            'title' => $this->daysUntilRenewal < 0
                ? __('notifications.subscription.days_overdue')
                : __('notifications.subscription.days_until_renewal'),
            'value' => $days . ' ' . $daysText,
            'short' => true,
        ];

        $fields[] = [
            'title' => __('notifications.subscription.renewal_date'),
            'value' => $this->subscription->next_renewal_date?->format('Y-m-d') ?? __('notifications.not_available'),
            'short' => true,
        ];

        $currency = $this->subscription->currency ?? 'EUR';
        $currencySymbol = $currency === 'EUR' ? '€' : ($currency === 'USD' ? '$' : $currency . ' ');
        $billingCycleKey = 'notifications.billing_cycle.' . ($this->subscription->billing_cycle ?? 'monthly');
        $billingCycleLabel = __($billingCycleKey);

        $fields[] = [
            'title' => __('notifications.subscription.price'),
            'value' => $currencySymbol . number_format($this->subscription->price, 2) . '/' . $billingCycleLabel,
            'short' => true,
        ];

        $statusKey = 'notifications.status.' . $this->subscription->status;
        $statusText = __($statusKey);

        $fields[] = [
            'title' => __('notifications.subscription.status'),
            'value' => $statusText,
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
