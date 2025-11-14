<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Subscription;
use Carbon\Carbon;

class AdvanceOverdueSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:advance-overdue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Avansează automat datele de reînnoire pentru abonamentele expirate';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Verificare abonamente expirate...');

        $today = Carbon::now()->startOfDay();

        // Get all active subscriptions that are overdue
        $overdueSubscriptions = Subscription::withoutGlobalScope('user')
            ->where('status', 'active')
            ->where('next_renewal_date', '<', $today)
            ->get();

        if ($overdueSubscriptions->isEmpty()) {
            $this->info('Nu s-au găsit abonamente expirate.');
            return 0;
        }

        $this->info("S-au găsit {$overdueSubscriptions->count()} abonament(e) expirat(e). Avansare date reînnoire...");

        $advancedCount = 0;

        foreach ($overdueSubscriptions as $subscription) {
            try {
                $oldDate = $subscription->next_renewal_date->format('Y-m-d');

                // Advance the subscription until it's in the future
                $subscription->advanceOverdueRenewals();

                $newDate = $subscription->fresh()->next_renewal_date->format('Y-m-d');

                $this->line("✓ {$subscription->vendor_name}: {$oldDate} → {$newDate}");
                $advancedCount++;
            } catch (\Exception $e) {
                $this->error("✗ Eroare la avansarea {$subscription->vendor_name}: {$e->getMessage()}");
            }
        }

        $this->info("S-au avansat cu succes {$advancedCount} abonament(e).");

        return 0;
    }
}
