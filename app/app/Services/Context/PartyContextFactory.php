<?php

namespace App\Services\Context;

use App\Models\Client;
use App\Models\Contract;
use App\Models\Offer;

/**
 * PartyContextFactory - Builds PartyContext from various data sources.
 *
 * This factory normalizes party data from:
 * - Client model (existing client)
 * - Offer model temp fields (prospect)
 * - Contract model temp fields (prospect)
 *
 * Priority chain: Client > Contract temp > Offer temp > empty
 */
class PartyContextFactory
{
    /**
     * Build context from an existing Client.
     */
    public static function fromClient(Client $client): PartyContext
    {
        return new PartyContext(
            companyName: $client->company_name ?? $client->name,
            name: $client->name,
            email: $client->email,
            phone: $client->phone,
            address: $client->address,
            taxId: $client->tax_id,
            registrationNumber: $client->registration_number,
            bankAccount: $client->bank_account ?? null,
            representative: $client->contact_person ?? $client->name,
        );
    }

    /**
     * Build context from Offer temp fields (prospect data).
     */
    public static function fromOffer(Offer $offer): PartyContext
    {
        return new PartyContext(
            companyName: $offer->temp_client_company ?? $offer->temp_client_name,
            name: $offer->temp_client_name,
            email: $offer->temp_client_email,
            phone: $offer->temp_client_phone,
            address: $offer->temp_client_address,
            taxId: $offer->temp_client_tax_id,
            registrationNumber: $offer->temp_client_registration_number,
            bankAccount: $offer->temp_client_bank_account,
            representative: $offer->temp_client_name,
        );
    }

    /**
     * Build context from Contract temp fields (prospect data).
     */
    public static function fromContract(Contract $contract): PartyContext
    {
        return new PartyContext(
            companyName: $contract->temp_client_company ?? $contract->temp_client_name,
            name: $contract->temp_client_name,
            email: $contract->temp_client_email,
            phone: $contract->temp_client_phone,
            address: $contract->temp_client_address,
            taxId: $contract->temp_client_tax_id,
            registrationNumber: $contract->temp_client_registration_number,
            bankAccount: $contract->temp_client_bank_account,
            representative: $contract->temp_client_name,
        );
    }

    /**
     * Resolve the best available source and build context.
     *
     * Strategy: Use Client as base, but override with temp fields when they exist.
     * This allows editing contract-specific data (like bank account) without
     * modifying the client record.
     *
     * @param Client|null $client Existing client (if any)
     * @param Contract|null $contract Contract with potential temp data
     * @param Offer|null $offer Offer with potential temp data
     * @return PartyContext Unified context with party data
     */
    public static function resolve(?Client $client, ?Contract $contract, ?Offer $offer): PartyContext
    {
        // Get base values from client if available
        $base = $client ? self::fromClient($client) : null;

        // Get temp values from contract or offer
        $tempName = $contract?->temp_client_name ?? $offer?->temp_client_name;
        $tempCompany = $contract?->temp_client_company ?? $offer?->temp_client_company;
        $tempEmail = $contract?->temp_client_email ?? $offer?->temp_client_email;
        $tempPhone = $contract?->temp_client_phone ?? $offer?->temp_client_phone;
        $tempAddress = $contract?->temp_client_address ?? $offer?->temp_client_address;
        $tempTaxId = $contract?->temp_client_tax_id ?? $offer?->temp_client_tax_id;
        $tempRegNumber = $contract?->temp_client_registration_number ?? $offer?->temp_client_registration_number;
        $tempBankAccount = $contract?->temp_client_bank_account ?? $offer?->temp_client_bank_account;

        // If we have a client, merge with temp overrides
        if ($base) {
            return new PartyContext(
                companyName: $tempCompany ?: $base->companyName,
                name: $tempName ?: $base->name,
                email: $tempEmail ?: $base->email,
                phone: $tempPhone ?: $base->phone,
                address: $tempAddress ?: $base->address,
                taxId: $tempTaxId ?: $base->taxId,
                registrationNumber: $tempRegNumber ?: $base->registrationNumber,
                bankAccount: $tempBankAccount ?: $base->bankAccount,
                representative: $tempName ?: $base->representative,
            );
        }

        // No client - use temp fields only
        if ($tempName) {
            return new PartyContext(
                companyName: $tempCompany ?? $tempName,
                name: $tempName,
                email: $tempEmail,
                phone: $tempPhone,
                address: $tempAddress,
                taxId: $tempTaxId,
                registrationNumber: $tempRegNumber,
                bankAccount: $tempBankAccount,
                representative: $tempName,
            );
        }

        // Fallback: Empty context
        return PartyContext::empty();
    }
}
