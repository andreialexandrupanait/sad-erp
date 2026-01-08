<?php

namespace App\Enums;

/**
 * Contract Status Enum
 *
 * Defines all valid status values for contracts with their labels, colors,
 * and allowed transitions.
 */
enum ContractStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Completed = 'completed';
    case Terminated = 'terminated';
    case Expired = 'expired';

    /**
     * Get human-readable label for the status.
     */
    public function label(): string
    {
        return match($this) {
            self::Draft => __('Draft'),
            self::Active => __('Active'),
            self::Completed => __('Completed'),
            self::Terminated => __('Terminated'),
            self::Expired => __('Expired'),
        };
    }

    /**
     * Get color class for UI display.
     */
    public function color(): string
    {
        return match($this) {
            self::Draft => 'gray',
            self::Active => 'green',
            self::Completed => 'blue',
            self::Terminated => 'red',
            self::Expired => 'yellow',
        };
    }

    /**
     * Get allowed transitions from this status.
     */
    public function allowedTransitions(): array
    {
        return match($this) {
            self::Draft => [self::Active, self::Terminated],
            self::Active => [self::Completed, self::Terminated, self::Expired],
            self::Completed => [], // Terminal state
            self::Terminated => [], // Terminal state
            self::Expired => [self::Active], // Can be renewed/reactivated
        };
    }

    /**
     * Check if transition to given status is allowed.
     */
    public function canTransitionTo(self $newStatus): bool
    {
        return in_array($newStatus, $this->allowedTransitions());
    }

    /**
     * Check if this is a terminal state (no further transitions except renewal).
     */
    public function isTerminal(): bool
    {
        return in_array($this, [self::Completed, self::Terminated]);
    }

    /**
     * Check if contract is currently active.
     */
    public function isActive(): bool
    {
        return $this === self::Active;
    }

    /**
     * Check if contract is in draft state.
     */
    public function isDraft(): bool
    {
        return $this === self::Draft;
    }

    /**
     * Get all status values as array.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get statuses for dropdown/filter options.
     */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn($case) => [
            $case->value => $case->label()
        ])->toArray();
    }
}
