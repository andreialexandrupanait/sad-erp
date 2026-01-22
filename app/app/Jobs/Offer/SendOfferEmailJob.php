<?php

namespace App\Jobs\Offer;

use App\Jobs\GenerateDocumentPdfJob;
use App\Models\Document;
use App\Models\Offer;
use App\Services\Offer\OfferService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendOfferEmailJob implements ShouldQueue
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
        public Offer $offer
    ) {}

    /**
     * Execute the job.
     */
    public function handle(OfferService $offerService): void
    {
        Log::info('SendOfferEmailJob: Starting', [
            'offer_id' => $this->offer->id,
            'offer_number' => $this->offer->offer_number,
        ]);

        try {
            // Generate PDF synchronously within this job (so it's ready for email)
            GenerateDocumentPdfJob::dispatchSync($this->offer, Document::TYPE_OFFER_SENT);

            // Send the email with PDF attachment
            $offerService->sendOfferEmail($this->offer);

            Log::info('SendOfferEmailJob: Completed successfully', [
                'offer_id' => $this->offer->id,
            ]);
        } catch (\Exception $e) {
            Log::error('SendOfferEmailJob: Failed', [
                'offer_id' => $this->offer->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('SendOfferEmailJob: Job permanently failed', [
            'offer_id' => $this->offer->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
