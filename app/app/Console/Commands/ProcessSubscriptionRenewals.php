<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Subscription;
use App\Models\Expense;
use App\Models\SubscriptionLog;
use Carbon\Carbon;

class ProcessSubscriptionRenewals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:process-renewals';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process automatic subscription renewals and create expenses for renewed subscriptions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::now()->startOfDay();
        
        // Get all subscriptions that are due for renewal today
        $subscriptions = Subscription::where('status', 'active')
            ->whereDate('next_renewal_date', '<=', $today)
            ->get();

        $renewed = 0;
        $cancelled = 0;
        $errors = 0;

        foreach ($subscriptions as $subscription) {
            try {
                if ($subscription->auto_renew) {
                    // Process automatic renewal
                    $this->info("Renewing subscription: {$subscription->vendor_name}");
                    
                    $oldDate = $subscription->next_renewal_date;
                    $newDate = $subscription->calculateNextRenewal();
                    
                    // Update renewal date
                    $subscription->updateRenewalDate($newDate, 'Automatic renewal');
                    
                    // Create expense record for the renewed subscription
                    $this->createExpenseForRenewal($subscription);
                    
                    $renewed++;
                } else {
                    // Subscription is set to not auto-renew, mark as cancelled
                    $this->info("Cancelling subscription: {$subscription->vendor_name}");
                    
                    $subscription->update(['status' => 'cancelled']);
                    
                    // Log the cancellation
                    SubscriptionLog::create([
                        'subscription_id' => $subscription->id,
                        'organization_id' => $subscription->organization_id,
                        'old_renewal_date' => $subscription->next_renewal_date,
                        'new_renewal_date' => $subscription->next_renewal_date,
                        'change_reason' => 'Auto-renewal disabled - subscription cancelled',
                        'changed_by_user_id' => null,
                        'changed_at' => now(),
                    ]);
                    
                    $cancelled++;
                }
            } catch (\Exception $e) {
                $this->error("Error processing {$subscription->vendor_name}: {$e->getMessage()}");
                $errors++;
            }
        }

        $this->info("Processed {$subscriptions->count()} subscriptions:");
        $this->info("- Renewed: {$renewed}");
        $this->info("- Cancelled: {$cancelled}");
        
        if ($errors > 0) {
            $this->error("- Errors: {$errors}");
        }

        return $errors > 0 ? 1 : 0;
    }

    /**
     * Create an expense record for a renewed subscription
     */
    protected function createExpenseForRenewal(Subscription $subscription)
    {
        // Check if Expense model exists
        if (!class_exists(Expense::class)) {
            $this->warn("Expense model not found - skipping expense creation for {$subscription->vendor_name}");
            return;
        }

        try {
            Expense::create([
                'organization_id' => $subscription->organization_id,
                'user_id' => $subscription->user_id,
                'created_by' => $subscription->created_by,
                'vendor' => $subscription->vendor_name,
                'amount' => $subscription->price,
                'currency' => $subscription->currency ?? 'RON',
                'category' => 'subscription',
                'description' => "Automatic renewal: {$subscription->vendor_name} ({$subscription->billing_cycle_label})",
                'payment_date' => now(),
                'status' => 'pending',
            ]);
            
            $this->info("Created expense record for {$subscription->vendor_name}");
        } catch (\Exception $e) {
            $this->warn("Could not create expense for {$subscription->vendor_name}: {$e->getMessage()}");
        }
    }
}
