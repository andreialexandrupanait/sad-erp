<?php

namespace App\Services\Contract;

use App\Models\Contract;
use App\Models\ContractAnnex;
use App\Models\Offer;
use App\Services\Notification\NotificationService;
use App\Services\Notification\Messages\ContractExpiringMessage;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Contract Service - Business logic for contract management.
 *
 * Handles:
 * - Contract creation from offers
 * - PDF generation for contracts and annexes
 * - Contract renewal and termination
 * - Annex management
 * - Expiry notifications
 */
class ContractService
{
    public function __construct(
        protected ?NotificationService $notificationService = null
    ) {}

    /**
     * Create a contract from an accepted offer.
     */
    public function createFromOffer(Offer $offer): Contract
    {
        if (!$offer->isAccepted()) {
            throw new \RuntimeException(__('Only accepted offers can be converted to contracts.'));
        }

        return DB::transaction(function () use ($offer) {
            $contract = Contract::create([
                'organization_id' => $offer->organization_id,
                'client_id' => $offer->client_id,
                'offer_id' => $offer->id,
                'template_id' => $offer->template_id,
                'contract_number' => Contract::generateContractNumber(),
                'title' => $offer->title,
                'description' => $offer->description,
                'total_value' => $offer->total,
                'currency' => $offer->currency,
                'status' => 'active',
                'start_date' => now(),
                'created_by' => auth()->id(),
            ]);

            // Link offer to contract
            $offer->update(['contract_id' => $contract->id]);

            // Generate PDF
            $this->generatePdf($contract);

            Log::info("Contract created from offer", [
                'contract_id' => $contract->id,
                'offer_id' => $offer->id,
            ]);

            return $contract;
        });
    }

    /**
     * Add an annex to a contract from an accepted offer.
     */
    public function addAnnexFromOffer(Contract $contract, Offer $offer): ContractAnnex
    {
        if (!$offer->isAccepted()) {
            throw new \RuntimeException(__('Only accepted offers can be added as annexes.'));
        }

        return DB::transaction(function () use ($contract, $offer) {
            $annexNumber = $contract->annexes()->count() + 1;

            $annex = ContractAnnex::create([
                'contract_id' => $contract->id,
                'offer_id' => $offer->id,
                'annex_number' => $annexNumber,
                'annex_code' => $contract->contract_number . '-A' . $annexNumber,
                'title' => $offer->title,
                'description' => $offer->description,
                'value' => $offer->total,
                'currency' => $offer->currency,
                'effective_date' => now(),
                'created_by' => auth()->id(),
            ]);

            // Update contract total value
            $contract->update([
                'total_value' => $contract->total_value + $offer->total,
            ]);

            // Link offer to contract
            $offer->update(['contract_id' => $contract->id]);

            // Generate PDF for annex
            $this->generateAnnexPdf($annex);

            Log::info("Contract annex added", [
                'contract_id' => $contract->id,
                'annex_id' => $annex->id,
                'offer_id' => $offer->id,
            ]);

            return $annex;
        });
    }

    /**
     * Generate PDF for a contract.
     */
    public function generatePdf(Contract $contract): string
    {
        $contract->load(['client', 'offer.items', 'template', 'annexes']);

        // Get template content or use default
        $template = $contract->template;
        $content = $template ? $template->render($this->getTemplateVariables($contract)) : null;

        // Generate PDF
        $pdf = Pdf::loadView('contracts.pdf', [
            'contract' => $contract,
            'content' => $content,
        ]);

        // Save PDF
        $filename = "contracts/{$contract->organization_id}/{$contract->contract_number}.pdf";
        $path = storage_path("app/{$filename}");

        // Ensure directory exists
        $directory = dirname($path);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $pdf->save($path);

        // Update contract with PDF path
        $contract->update(['pdf_path' => $filename]);

        return $filename;
    }

    /**
     * Generate PDF for a contract annex.
     */
    public function generateAnnexPdf(ContractAnnex $annex): string
    {
        $annex->load(['contract.client', 'offer.items']);

        // Generate PDF
        $pdf = Pdf::loadView('contracts.annex-pdf', [
            'annex' => $annex,
            'contract' => $annex->contract,
        ]);

        // Save PDF
        $filename = "contracts/{$annex->contract->organization_id}/annexes/{$annex->annex_code}.pdf";
        $path = storage_path("app/{$filename}");

        // Ensure directory exists
        $directory = dirname($path);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $pdf->save($path);

        // Update annex with PDF path
        $annex->update(['pdf_path' => $filename]);

        return $filename;
    }

    /**
     * Terminate a contract.
     */
    public function terminate(Contract $contract, ?string $reason = null): void
    {
        if (!$contract->isActive()) {
            throw new \RuntimeException(__('Only active contracts can be terminated.'));
        }

        $contract->update([
            'status' => 'terminated',
            'terminated_at' => now(),
            'termination_reason' => $reason,
        ]);

        Log::info("Contract terminated", [
            'contract_id' => $contract->id,
            'reason' => $reason,
        ]);
    }

    /**
     * Renew a contract.
     */
    public function renew(Contract $contract, ?array $data = []): Contract
    {
        return DB::transaction(function () use ($contract, $data) {
            // Expire current contract
            $contract->update([
                'status' => 'expired',
            ]);

            // Create new contract
            $newContract = $contract->replicate([
                'contract_number',
                'status',
                'start_date',
                'end_date',
                'pdf_path',
                'signed_at',
                'terminated_at',
                'termination_reason',
            ]);

            $newContract->contract_number = Contract::generateContractNumber();
            $newContract->status = 'active';
            $newContract->start_date = $data['start_date'] ?? ($contract->end_date ?? now());
            $newContract->end_date = $data['end_date'] ?? null;
            $newContract->parent_contract_id = $contract->id;
            $newContract->created_by = auth()->id();

            if (isset($data['total_value'])) {
                $newContract->total_value = $data['total_value'];
            }

            $newContract->save();

            // Generate PDF
            $this->generatePdf($newContract);

            Log::info("Contract renewed", [
                'old_contract_id' => $contract->id,
                'new_contract_id' => $newContract->id,
            ]);

            return $newContract;
        });
    }

    /**
     * Get contracts expiring within days.
     */
    public function getExpiringContracts(int $days = 30, ?int $organizationId = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = Contract::where('status', 'active')
            ->whereNotNull('end_date')
            ->whereBetween('end_date', [now(), now()->addDays($days)]);

        if ($organizationId) {
            $query->where('organization_id', $organizationId);
        }

        return $query->with('client')->orderBy('end_date')->get();
    }

    /**
     * Send expiry notifications.
     */
    public function sendExpiryNotifications(int $days = 30): int
    {
        $contracts = $this->getExpiringContracts($days);
        $count = 0;

        foreach ($contracts as $contract) {
            if ($this->notificationService) {
                $message = new ContractExpiringMessage($contract, $contract->days_until_expiry);
                $this->notificationService->send($message, null, $contract->organization_id);
                $count++;
            }
        }

        Log::info("Contract expiry notifications sent", ['count' => $count]);

        return $count;
    }

    /**
     * Get template variables for a contract.
     */
    protected function getTemplateVariables(Contract $contract): array
    {
        $client = $contract->client;

        return [
            // Contract variables
            'contract_number' => $contract->contract_number,
            'contract_title' => $contract->title,
            'start_date' => $contract->start_date?->format('d.m.Y'),
            'end_date' => $contract->end_date?->format('d.m.Y'),
            'total_value' => number_format($contract->total_value, 2, ',', '.'),
            'currency' => $contract->currency,
            'description' => $contract->description ?? '',

            // Client variables
            'client_name' => $client?->display_name ?? '',
            'client_company' => $client?->company_name ?? '',
            'client_email' => $client?->email ?? '',
            'client_phone' => $client?->phone ?? '',
            'client_address' => $client?->full_address ?? '',
            'client_fiscal_code' => $client?->fiscal_code ?? '',
            'client_registration_number' => $client?->registration_number ?? '',

            // Company variables
            'company_name' => config('app.name'),
            'company_email' => config('mail.from.address'),

            // Date variables
            'current_date' => now()->format('d.m.Y'),
            'current_year' => now()->year,
        ];
    }

    /**
     * Get contract statistics.
     */
    public function getStatistics(?int $organizationId = null): array
    {
        $organizationId = $organizationId ?? auth()->user()->organization_id;

        return Contract::where('organization_id', $organizationId)
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN status = 'expired' THEN 1 ELSE 0 END) as expired,
                SUM(CASE WHEN status = 'terminated' THEN 1 ELSE 0 END) as terminated,
                SUM(CASE WHEN status = 'active' THEN total_value ELSE 0 END) as active_value,
                SUM(total_value) as total_value
            ")
            ->first()
            ->toArray();
    }
}
