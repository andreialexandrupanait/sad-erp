<?php

namespace App\Services\Context;

/**
 * PartyContext - Unified data transfer object for client/prospect party data.
 *
 * This DTO normalizes party-related data regardless of whether it comes from:
 * - An existing Client record
 * - Temporary prospect data on an Offer
 * - Temporary prospect data on a Contract
 *
 * Used by VariableRegistry to resolve client variables consistently.
 */
class PartyContext
{
    public function __construct(
        public readonly ?string $companyName,
        public readonly ?string $name,
        public readonly ?string $email,
        public readonly ?string $phone,
        public readonly ?string $address,
        public readonly ?string $taxId,
        public readonly ?string $registrationNumber,
        public readonly ?string $bankAccount,
        public readonly ?string $representative,
    ) {}

    /**
     * Create an empty context (all null values).
     */
    public static function empty(): self
    {
        return new self(
            companyName: null,
            name: null,
            email: null,
            phone: null,
            address: null,
            taxId: null,
            registrationNumber: null,
            bankAccount: null,
            representative: null,
        );
    }

    /**
     * Check if the context has any data.
     */
    public function hasData(): bool
    {
        return $this->companyName !== null
            || $this->name !== null
            || $this->email !== null;
    }
}
