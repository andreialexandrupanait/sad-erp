<?php

namespace App\Services\Notification\Messages;

use App\Models\Client;
use App\Models\SettingOption;

class ClientStatusChangedMessage extends NotificationMessage
{
    protected ?string $oldStatusLabel = null;
    protected ?string $newStatusLabel = null;

    public function __construct(
        protected Client $client,
        protected ?int $oldStatusId,
        protected int $newStatusId
    ) {
        $this->resolveStatusLabels();
    }

    public function getTitle(): string
    {
        return "Client Status Changed: {$this->client->display_name}";
    }

    public function getBody(): string
    {
        $oldStatus = $this->oldStatusLabel ?? 'None';
        $newStatus = $this->newStatusLabel ?? 'Unknown';

        return "The status of {$this->client->display_name} has been changed from \"{$oldStatus}\" to \"{$newStatus}\".";
    }

    public function getPriority(): string
    {
        return 'normal';
    }

    public function getNotificationType(): string
    {
        return 'client_status_changed';
    }

    public function getEntityType(): ?string
    {
        return 'Client';
    }

    public function getEntityId(): ?int
    {
        return $this->client->id;
    }

    public function toArray(): array
    {
        return [
            'client_id' => $this->client->id,
            'client_name' => $this->client->display_name,
            'client_slug' => $this->client->slug,
            'old_status_id' => $this->oldStatusId,
            'old_status_label' => $this->oldStatusLabel,
            'new_status_id' => $this->newStatusId,
            'new_status_label' => $this->newStatusLabel,
            'company_name' => $this->client->company_name,
            'total_incomes' => $this->client->total_incomes,
        ];
    }

    public function getFields(): array
    {
        $fields = [];

        $fields[] = [
            'title' => 'Previous Status',
            'value' => $this->oldStatusLabel ?? 'None',
            'short' => true,
        ];

        $fields[] = [
            'title' => 'New Status',
            'value' => $this->newStatusLabel ?? 'Unknown',
            'short' => true,
        ];

        if ($this->client->company_name) {
            $fields[] = [
                'title' => 'Company',
                'value' => $this->client->company_name,
                'short' => true,
            ];
        }

        if ($this->client->total_incomes && $this->client->total_incomes > 0) {
            $fields[] = [
                'title' => 'Total Revenue',
                'value' => '€' . number_format($this->client->total_incomes, 2),
                'short' => true,
            ];
        }

        return $fields;
    }

    public function getUrl(): ?string
    {
        return url("/clients/{$this->client->slug}");
    }

    public function getColor(): string
    {
        return '#2196f3'; // Blue - informational
    }

    public function getIcon(): string
    {
        return ':bust_in_silhouette:';
    }

    /**
     * Client status changes are not interval-based.
     */
    public function isIntervalBased(): bool
    {
        return false;
    }

    /**
     * Resolve status labels from SettingOption.
     */
    protected function resolveStatusLabels(): void
    {
        if ($this->oldStatusId) {
            $oldStatus = SettingOption::find($this->oldStatusId);
            $this->oldStatusLabel = $oldStatus?->label;
        }

        $newStatus = SettingOption::find($this->newStatusId);
        $this->newStatusLabel = $newStatus?->label;
    }
}
