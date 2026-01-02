<?php

namespace App\Enums;

/**
 * Offer Status Enum
 *
 * Defines all valid status values for offers with their labels and colors.
 */
enum OfferStatus: string
{
    case Draft = 'draft';
    case Sent = 'sent';
    case Viewed = 'viewed';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    case Expired = 'expired';

    /**
     * Get human-readable label for the status.
     */
    public function label(): string
    {
        return match($this) {
            self::Draft => __('Draft'),
            self::Sent => __('Sent'),
            self::Viewed => __('Viewed'),
            self::Accepted => __('Accepted'),
            self::Rejected => __('Rejected'),
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
            self::Sent => 'blue',
            self::Viewed => 'yellow',
            self::Accepted => 'green',
            self::Rejected => 'red',
            self::Expired => 'orange',
        };
    }

    /**
     * Check if offer can be sent.
     */
    public function canBeSent(): bool
    {
        return in_array($this, [self::Draft, self::Viewed]);
    }

    /**
     * Check if offer can be accepted.
     */
    public function canBeAccepted(): bool
    {
        return in_array($this, [self::Sent, self::Viewed]);
    }

    /**
     * Check if offer can be rejected.
     */
    public function canBeRejected(): bool
    {
        return in_array($this, [self::Sent, self::Viewed]);
    }

    /**
     * Check if this is a terminal state (no further transitions).
     */
    public function isTerminal(): bool
    {
        return in_array($this, [self::Accepted, self::Rejected, self::Expired]);
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
