<?php

namespace App\Exports;

use App\Models\Offer;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OffersExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected ?array $offerIds;
    protected ?string $status;

    public function __construct(?array $offerIds = null, ?string $status = null)
    {
        $this->offerIds = $offerIds;
        $this->status = $status;
    }

    public function collection(): Collection
    {
        $query = Offer::with(['client', 'creator']);

        // Filter by specific IDs
        if ($this->offerIds && count($this->offerIds) > 0) {
            $query->whereIn('id', $this->offerIds);
        }

        // Filter by status
        if ($this->status) {
            $query->where('status', $this->status);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            __('Offer Number'),
            __('Title'),
            __('Client'),
            __('Status'),
            __('Subtotal'),
            __('Discount'),
            __('Total'),
            __('Currency'),
            __('Valid Until'),
            __('Created At'),
            __('Sent At'),
            __('Accepted At'),
            __('Created By'),
        ];
    }

    public function map($offer): array
    {
        $clientName = $offer->client?->display_name ?? $offer->temp_client_name ?? '-';

        return [
            $offer->offer_number,
            $offer->title ?? '-',
            $clientName,
            $offer->status_label,
            number_format($offer->subtotal, 2, '.', ''),
            number_format($offer->discount_amount ?? 0, 2, '.', ''),
            number_format($offer->total, 2, '.', ''),
            $offer->currency,
            $offer->valid_until?->format('Y-m-d') ?? '-',
            $offer->created_at?->format('Y-m-d H:i'),
            $offer->sent_at?->format('Y-m-d H:i') ?? '-',
            $offer->accepted_at?->format('Y-m-d H:i') ?? '-',
            $offer->creator?->name ?? '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E2E8F0'],
                ],
            ],
        ];
    }
}
