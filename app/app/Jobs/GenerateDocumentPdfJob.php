<?php

namespace App\Jobs;

use App\Models\Document;
use App\Models\Offer;
use App\Models\Contract;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GenerateDocumentPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Model $documentable,
        public string $type
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('GenerateDocumentPdfJob: Starting PDF generation', [
            'documentable_type' => get_class($this->documentable),
            'documentable_id' => $this->documentable->id,
            'type' => $this->type,
        ]);

        try {
            $pdf = $this->generatePdf();
            $path = $this->savePdf($pdf);
            $this->createDocumentRecord($path);

            Log::info('GenerateDocumentPdfJob: PDF generated successfully', [
                'path' => $path,
            ]);
        } catch (\Exception $e) {
            Log::error('GenerateDocumentPdfJob: Failed to generate PDF', [
                'error' => $e->getMessage(),
                'documentable_type' => get_class($this->documentable),
                'documentable_id' => $this->documentable->id,
            ]);

            throw $e;
        }
    }

    /**
     * Generate the PDF content.
     */
    protected function generatePdf()
    {
        $documentable = $this->documentable;

        if ($documentable instanceof Offer) {
            return $this->generateOfferPdf($documentable);
        }

        if ($documentable instanceof Contract) {
            return $this->generateContractPdf($documentable);
        }

        throw new \RuntimeException('Unsupported documentable type: ' . get_class($documentable));
    }

    /**
     * Generate PDF for an offer.
     */
    protected function generateOfferPdf(Offer $offer)
    {
        $offer->load(['client', 'items', 'template', 'organization']);

        // Get template content if available
        $content = null;
        if ($offer->template) {
            $content = $offer->template->render($this->getOfferVariables($offer));
        }

        return Pdf::loadView('offers.pdf', [
            'offer' => $offer,
            'content' => $content,
            'type' => $this->type,
        ])->setPaper('a4');
    }

    /**
     * Generate PDF for a contract.
     */
    protected function generateContractPdf(Contract $contract)
    {
        $contract->load(['client', 'template', 'organization']);

        // Get template content if available
        $content = $contract->content;
        if ($contract->template) {
            $content = $contract->template->render($this->getContractVariables($contract));
        }

        return Pdf::loadView('contracts.pdf', [
            'contract' => $contract,
            'content' => $content,
        ])->setPaper('a4');
    }

    /**
     * Save the PDF to storage.
     */
    protected function savePdf($pdf): string
    {
        $documentable = $this->documentable;
        $year = $documentable->created_at?->year ?? date("Y");

        $number = match (true) {
            $documentable instanceof Offer => $documentable->offer_number,
            $documentable instanceof Contract => $documentable->contract_number,
            default => $documentable->id,
        };

        // Sanitize the number for use in filename
        $safeNumber = preg_replace("/[^a-zA-Z0-9_-]/", "_", $number);

        $path = Document::generatePath($this->type, $documentable->organization_id, $safeNumber, $year);

        // Get the documents disk (R2 when enabled, local otherwise)
        $disk = config("filesystems.documents_disk", "documents");

        // Save the PDF using Storage facade
        $pdfContent = $pdf->output();
        Storage::disk($disk)->put($path, $pdfContent);

        return $path;
    }

    /**
     * Create a document record in the database.
     */
    protected function createDocumentRecord(string $path): Document
    {
        $documentable = $this->documentable;

        $number = match (true) {
            $documentable instanceof Offer => $documentable->offer_number,
            $documentable instanceof Contract => $documentable->contract_number,
            default => (string) $documentable->id,
        };

        return Document::create([
            'organization_id' => $documentable->organization_id,
            'documentable_type' => get_class($documentable),
            'documentable_id' => $documentable->id,
            'type' => $this->type,
            'file_path' => $path,
            'file_name' => $number . '.pdf',
            'file_size' => Storage::disk(config('filesystems.documents_disk', 'documents'))->size($path),
            'generated_at' => now(),
        ]);
    }

    /**
     * Get template variables for an offer.
     */
    protected function getOfferVariables(Offer $offer): array
    {
        $client = $offer->client;
        $organization = $offer->organization;

        return [
            // Offer variables
            'offer_number' => $offer->offer_number,
            'offer_title' => $offer->title,
            'offer_date' => $offer->created_at?->format('d.m.Y'),
            'valid_until' => $offer->valid_until?->format('d.m.Y'),
            'subtotal' => number_format($offer->subtotal ?? 0, 2, ',', '.'),
            'discount_amount' => number_format($offer->discount_amount ?? 0, 2, ',', '.'),
            'discount_percent' => $offer->discount_percent,
            'total' => number_format($offer->total ?? 0, 2, ',', '.'),
            'currency' => $offer->currency,
            'notes' => $offer->notes ?? '',
            'terms' => $offer->terms ?? '',
            'introduction' => $offer->introduction ?? '',

            // Client variables
            'client_name' => $client?->display_name ?? $client?->name ?? '',
            'client_company' => $client?->company_name ?? '',
            'client_email' => $client?->email ?? '',
            'client_phone' => $client?->phone ?? '',
            'client_address' => $client?->address ?? '',
            'client_tax_id' => $client?->tax_id ?? '',

            // Organization variables
            'organization_name' => $organization?->name ?? config('app.name'),
            'organization_email' => $organization?->email ?? config('mail.from.address'),
            'organization_phone' => $organization?->phone ?? '',
            'organization_address' => $organization?->address ?? '',

            // Date variables
            'current_date' => now()->format('d.m.Y'),
            'current_year' => now()->year,

            // Services table (formatted)
            'services_table' => $this->formatServicesTable($offer),
        ];
    }

    /**
     * Get template variables for a contract.
     */
    protected function getContractVariables(Contract $contract): array
    {
        $client = $contract->client;
        $organization = $contract->organization;

        return [
            // Contract variables
            'contract_number' => $contract->contract_number,
            'contract_title' => $contract->title,
            'contract_start_date' => $contract->start_date?->format('d.m.Y'),
            'contract_end_date' => $contract->end_date?->format('d.m.Y'),
            'contract_total_value' => number_format($contract->total_value ?? 0, 2, ',', '.'),
            'currency' => $contract->currency,

            // Client variables
            'client_name' => $client?->display_name ?? $client?->name ?? '',
            'client_company' => $client?->company_name ?? '',
            'client_email' => $client?->email ?? '',
            'client_phone' => $client?->phone ?? '',
            'client_address' => $client?->address ?? '',
            'client_tax_id' => $client?->tax_id ?? '',

            // Organization variables
            'organization_name' => $organization?->name ?? config('app.name'),
            'organization_email' => $organization?->email ?? config('mail.from.address'),
            'organization_phone' => $organization?->phone ?? '',
            'organization_address' => $organization?->address ?? '',

            // Date variables
            'current_date' => now()->format('d.m.Y'),
            'current_year' => now()->year,
        ];
    }

    /**
     * Format the services table as HTML.
     */
    protected function formatServicesTable(Offer $offer): string
    {
        if ($offer->items->isEmpty()) {
            return '';
        }

        $html = '<table style="width: 100%; border-collapse: collapse;">';
        $html .= '<thead><tr>';
        $html .= '<th style="border: 1px solid #ddd; padding: 8px; text-align: left;">' . __('Service') . '</th>';
        $html .= '<th style="border: 1px solid #ddd; padding: 8px; text-align: center;">' . __('Qty') . '</th>';
        $html .= '<th style="border: 1px solid #ddd; padding: 8px; text-align: right;">' . __('Unit Price') . '</th>';
        $html .= '<th style="border: 1px solid #ddd; padding: 8px; text-align: right;">' . __('Total') . '</th>';
        $html .= '</tr></thead><tbody>';

        foreach ($offer->items as $item) {
            $html .= '<tr>';
            $html .= '<td style="border: 1px solid #ddd; padding: 8px;">';
            $html .= e($item->title);
            if ($item->description) {
                $html .= '<br><small style="color: #666;">' . e($item->description) . '</small>';
            }
            $html .= '</td>';
            $html .= '<td style="border: 1px solid #ddd; padding: 8px; text-align: center;">' . $item->quantity . ' ' . e($item->unit ?? '') . '</td>';
            $html .= '<td style="border: 1px solid #ddd; padding: 8px; text-align: right;">' . number_format($item->unit_price ?? 0, 2, ',', '.') . '</td>';
            $html .= '<td style="border: 1px solid #ddd; padding: 8px; text-align: right;">' . number_format($item->total_price ?? 0, 2, ',', '.') . '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';

        return $html;
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('GenerateDocumentPdfJob: Job failed permanently', [
            'error' => $exception->getMessage(),
            'documentable_type' => get_class($this->documentable),
            'documentable_id' => $this->documentable->id,
            'type' => $this->type,
        ]);
    }
}
