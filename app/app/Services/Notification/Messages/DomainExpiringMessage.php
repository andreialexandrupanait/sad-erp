<?php

namespace App\Services\Notification\Messages;

use App\Models\Domain;

class DomainExpiringMessage extends NotificationMessage
{
    protected string $notificationType;

    public function __construct(
        protected Domain $domain,
        protected int $daysUntilExpiry,
        protected bool $isExpired = false
    ) {
        $this->notificationType = $this->determineNotificationType();
    }

    public function getTitle(): string
    {
        if ($this->isExpired) {
            return "Domain Expired: {$this->domain->domain_name}";
        }

        if ($this->daysUntilExpiry === 0) {
            return "Domain Expires Today: {$this->domain->domain_name}";
        }

        return "Domain Expiring Soon: {$this->domain->domain_name}";
    }

    public function getBody(): string
    {
        if ($this->isExpired) {
            $daysAgo = abs($this->daysUntilExpiry);
            return "The domain {$this->domain->domain_name} expired {$daysAgo} day(s) ago. Immediate action required!";
        }

        if ($this->daysUntilExpiry === 0) {
            return "The domain {$this->domain->domain_name} expires TODAY. Renew immediately to avoid service interruption.";
        }

        if ($this->daysUntilExpiry === 1) {
            return "The domain {$this->domain->domain_name} expires TOMORROW. Renew now to avoid service interruption.";
        }

        return "The domain {$this->domain->domain_name} will expire in {$this->daysUntilExpiry} days.";
    }

    public function getPriority(): string
    {
        if ($this->isExpired) {
            return 'urgent';
        }

        if ($this->daysUntilExpiry <= 3) {
            return 'urgent';
        }

        if ($this->daysUntilExpiry <= 7) {
            return 'high';
        }

        return 'normal';
    }

    public function getNotificationType(): string
    {
        return $this->notificationType;
    }

    public function getEntityType(): ?string
    {
        return 'Domain';
    }

    public function getEntityId(): ?int
    {
        return $this->domain->id;
    }

    public function toArray(): array
    {
        return [
            'domain_id' => $this->domain->id,
            'domain_name' => $this->domain->domain_name,
            'days_until_expiry' => $this->daysUntilExpiry,
            'is_expired' => $this->isExpired,
            'expiry_date' => $this->domain->expiry_date?->format('Y-m-d'),
            'client_id' => $this->domain->client_id,
            'client_name' => $this->domain->client?->display_name,
            'registrar' => $this->domain->registrar,
            'annual_cost' => $this->domain->annual_cost,
            'auto_renew' => $this->domain->auto_renew,
        ];
    }

    public function getFields(): array
    {
        $fields = [];

        if ($this->domain->client) {
            $fields[] = [
                'title' => 'Client',
                'value' => $this->domain->client->display_name,
                'short' => true,
            ];
        }

        $fields[] = [
            'title' => $this->isExpired ? 'Days Overdue' : 'Days Until Expiry',
            'value' => abs($this->daysUntilExpiry) . ' day(s)',
            'short' => true,
        ];

        $fields[] = [
            'title' => 'Expiry Date',
            'value' => $this->domain->expiry_date?->format('Y-m-d') ?? 'N/A',
            'short' => true,
        ];

        if ($this->domain->registrar) {
            $fields[] = [
                'title' => 'Registrar',
                'value' => $this->domain->registrar,
                'short' => true,
            ];
        }

        if ($this->domain->annual_cost) {
            $fields[] = [
                'title' => 'Annual Cost',
                'value' => '€' . number_format($this->domain->annual_cost, 2),
                'short' => true,
            ];
        }

        $fields[] = [
            'title' => 'Auto-Renew',
            'value' => $this->domain->auto_renew ? 'Yes' : 'No',
            'short' => true,
        ];

        return $fields;
    }

    public function getUrl(): ?string
    {
        return url("/domains/{$this->domain->id}");
    }

    public function getColor(): string
    {
        if ($this->isExpired) {
            return '#d32f2f'; // Red
        }

        return match (true) {
            $this->daysUntilExpiry <= 3 => '#d32f2f', // Red
            $this->daysUntilExpiry <= 7 => '#ff9800', // Orange
            $this->daysUntilExpiry <= 14 => '#ffc107', // Yellow
            default => '#2196f3', // Blue
        };
    }

    public function getIcon(): string
    {
        if ($this->isExpired) {
            return ':rotating_light:';
        }

        return match (true) {
            $this->daysUntilExpiry <= 3 => ':rotating_light:',
            $this->daysUntilExpiry <= 7 => ':warning:',
            default => ':globe_with_meridians:',
        };
    }

    protected function determineNotificationType(): string
    {
        if ($this->isExpired) {
            $daysOverdue = abs($this->daysUntilExpiry);
            return $daysOverdue === 0 ? 'domain_expired' : "domain_expired_{$daysOverdue}d";
        }

        return match ($this->daysUntilExpiry) {
            30 => 'domain_expiring_30d',
            14 => 'domain_expiring_14d',
            7 => 'domain_expiring_7d',
            3 => 'domain_expiring_3d',
            1 => 'domain_expiring_1d',
            0 => 'domain_expiring_today',
            default => "domain_expiring_{$this->daysUntilExpiry}d",
        };
    }
}
