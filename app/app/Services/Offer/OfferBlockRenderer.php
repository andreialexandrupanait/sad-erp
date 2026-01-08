<?php

namespace App\Services\Offer;

use App\Models\Offer;
use Illuminate\Support\Facades\Storage;

/**
 * Service for rendering offer blocks with variable injection.
 *
 * This service handles the replacement of {{variable}} placeholders
 * with actual values from the offer, client, and organization.
 */
class OfferBlockRenderer
{
    protected Offer $offer;
    protected array $variables = [];

    public function __construct(Offer $offer)
    {
        $this->offer = $offer;
        $this->buildVariables();
    }

    /**
     * Build the variables map from offer, client, and organization.
     */
    protected function buildVariables(): void
    {
        $client = $this->offer->client;
        $org = $this->offer->organization;

        $this->variables = [
            // Company variables
            'company.name' => $org?->name ?? config('app.name'),
            'company.email' => $org?->email ?? '',
            'company.phone' => $org?->phone ?? '',
            'company.address' => $org?->address ?? '',
            'company.tax_id' => $org?->tax_id ?? $org?->registration_number ?? '',
            'company.registration_number' => $org?->registration_number ?? '',
            'company.logo' => $org?->logo ? Storage::url($org->logo) : null,
            'company.logo_path' => $org?->logo ? storage_path('app/public/' . $org->logo) : null,

            // Client variables
            'client.name' => $client?->name ?? '',
            'client.company' => $client?->company_name ?? $client?->name ?? '',
            'client.email' => $client?->email ?? '',
            'client.phone' => $client?->phone ?? '',
            'client.address' => $client?->address ?? '',
            'client.tax_id' => $client?->tax_id ?? '',
            'client.contact' => $client?->contact_person ?? '',

            // Offer variables
            'offer.id' => $this->offer->id,
            'offer.number' => $this->offer->offer_number,
            'offer.title' => $this->offer->title ?? __('Service Proposal'),
            'offer.date' => $this->offer->created_at?->format('d.m.Y') ?? now()->format('d.m.Y'),
            'offer.valid_until' => $this->offer->valid_until?->format('d.m.Y') ?? '',
            'offer.subtotal' => $this->formatCurrency($this->offer->subtotal),
            'offer.subtotal_raw' => $this->offer->subtotal ?? 0,
            'offer.discount_percent' => $this->offer->discount_percent ?? 0,
            'offer.discount_amount' => $this->formatCurrency($this->offer->discount_amount ?? 0),
            'offer.discount_amount_raw' => $this->offer->discount_amount ?? 0,
            'offer.total' => $this->formatCurrency($this->offer->total),
            'offer.total_raw' => $this->offer->total ?? 0,
            'offer.currency' => $this->offer->currency ?? 'RON',
            'offer.notes' => $this->offer->notes ?? '',
            'offer.terms' => $this->offer->terms ?? '',
            'offer.introduction' => $this->offer->introduction ?? '',
            'offer.status' => $this->offer->status,
            'offer.public_token' => $this->offer->public_token,

            // URLs (for acceptance block)
            'offer.public_url' => $this->offer->public_token
                ? route('offers.public', $this->offer->public_token)
                : '',
            'offer.acceptance_url' => $this->offer->public_token
                ? route('offers.public.accept', $this->offer->public_token)
                : '',
            'offer.rejection_url' => $this->offer->public_token
                ? route('offers.public.reject', $this->offer->public_token)
                : '',

            // Date variables
            'current.date' => now()->format('d.m.Y'),
            'current.year' => (string) now()->year,
            'current.month' => now()->format('F'),
            'current.day' => (string) now()->day,
        ];
    }

    /**
     * Format a number as currency.
     */
    protected function formatCurrency(float|int|null $amount): string
    {
        return number_format($amount ?? 0, 2, ',', '.');
    }

    /**
     * Render a string by replacing {{variable}} placeholders.
     */
    public function render(string $content): string
    {
        foreach ($this->variables as $key => $value) {
            $content = str_replace('{{' . $key . '}}', (string) ($value ?? ''), $content);
        }
        return $content;
    }

    /**
     * Get a specific variable value.
     */
    public function getVariable(string $key, mixed $default = null): mixed
    {
        return $this->variables[$key] ?? $default;
    }

    /**
     * Get all variables.
     */
    public function getVariables(): array
    {
        return $this->variables;
    }

    /**
     * Set a custom variable.
     */
    public function setVariable(string $key, mixed $value): self
    {
        $this->variables[$key] = $value;
        return $this;
    }

    /**
     * Render a block by processing all text fields in its data.
     */
    public function renderBlock(array $block): array
    {
        $processed = $block;
        if (isset($processed['data'])) {
            $processed['data'] = $this->processData($processed['data']);
        }
        return $processed;
    }

    /**
     * Recursively process data to replace variables.
     */
    protected function processData(mixed $data): mixed
    {
        if (is_string($data)) {
            return $this->render($data);
        }

        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->processData($value);
            }
        }

        return $data;
    }

    /**
     * Render all blocks in an array.
     */
    public function renderBlocks(array $blocks): array
    {
        return array_map(fn($block) => $this->renderBlock($block), $blocks);
    }

    /**
     * Get the offer being rendered.
     */
    public function getOffer(): Offer
    {
        return $this->offer;
    }

    /**
     * Generate a services table HTML for PDF.
     */
    public function getServicesTableHtml(): string
    {
        $items = $this->offer->items;
        if ($items->isEmpty()) {
            return '';
        }

        $html = '<table style="width: 100%; border-collapse: collapse;">';
        $html .= '<thead><tr style="background: #f8fafc;">';
        $html .= '<th style="padding: 10px; text-align: left; border-bottom: 2px solid #e2e8f0;">' . __('Service') . '</th>';
        $html .= '<th style="padding: 10px; text-align: center; border-bottom: 2px solid #e2e8f0;">' . __('Qty') . '</th>';
        $html .= '<th style="padding: 10px; text-align: right; border-bottom: 2px solid #e2e8f0;">' . __('Unit Price') . '</th>';
        $html .= '<th style="padding: 10px; text-align: right; border-bottom: 2px solid #e2e8f0;">' . __('Total') . '</th>';
        $html .= '</tr></thead>';
        $html .= '<tbody>';

        foreach ($items as $item) {
            $html .= '<tr>';
            $html .= '<td style="padding: 10px; border-bottom: 1px solid #e2e8f0;">';
            $html .= '<strong>' . e($item->title) . '</strong>';
            if ($item->description) {
                $html .= '<br><span style="font-size: 9pt; color: #64748b;">' . e($item->description) . '</span>';
            }
            $html .= '</td>';
            $html .= '<td style="padding: 10px; text-align: center; border-bottom: 1px solid #e2e8f0;">' . $item->quantity . ' ' . __($item->unit) . '</td>';
            $html .= '<td style="padding: 10px; text-align: right; border-bottom: 1px solid #e2e8f0;">' . $this->formatCurrency($item->unit_price) . ' ' . $item->currency . '</td>';
            $html .= '<td style="padding: 10px; text-align: right; border-bottom: 1px solid #e2e8f0; font-weight: bold;">' . $this->formatCurrency($item->total_price) . ' ' . $item->currency . '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';

        return $html;
    }

    /**
     * Get summary data for the summary block.
     */
    public function getSummaryData(): array
    {
        return [
            'subtotal' => $this->offer->subtotal ?? 0,
            'subtotal_formatted' => $this->formatCurrency($this->offer->subtotal),
            'discount_percent' => $this->offer->discount_percent ?? 0,
            'discount_amount' => $this->offer->discount_amount ?? 0,
            'discount_amount_formatted' => $this->formatCurrency($this->offer->discount_amount ?? 0),
            'total' => $this->offer->total ?? 0,
            'total_formatted' => $this->formatCurrency($this->offer->total),
            'currency' => $this->offer->currency ?? 'RON',
        ];
    }
}
