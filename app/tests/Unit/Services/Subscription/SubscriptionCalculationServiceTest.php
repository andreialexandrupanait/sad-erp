<?php

namespace Tests\Unit\Services\Subscription;

use Tests\TestCase;
use App\Services\Subscription\SubscriptionCalculationService;
use App\Models\Subscription;
use App\Models\SubscriptionLog;
use App\Models\User;
use App\Models\Organization;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SubscriptionCalculationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected SubscriptionCalculationService $service;
    protected Organization $organization;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organization = Organization::factory()->create();
        $this->user = User::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $this->actingAs($this->user);

        $this->service = new SubscriptionCalculationService();
    }

    /** @test */
    public function it_calculates_next_renewal_for_weekly_cycle()
    {
        $subscription = Subscription::factory()->create([
            'organization_id' => $this->organization->id,
            'billing_cycle' => 'weekly',
            'next_renewal_date' => '2023-12-01',
        ]);

        $result = $this->service->calculateNextRenewal($subscription);

        $this->assertEquals('2023-12-08', $result->format('Y-m-d'));
    }

    /** @test */
    public function it_calculates_next_renewal_for_monthly_cycle()
    {
        $subscription = Subscription::factory()->create([
            'organization_id' => $this->organization->id,
            'billing_cycle' => 'monthly',
            'next_renewal_date' => '2023-12-01',
        ]);

        $result = $this->service->calculateNextRenewal($subscription);

        $this->assertEquals('2024-01-01', $result->format('Y-m-d'));
    }

    /** @test */
    public function it_calculates_next_renewal_for_annual_cycle()
    {
        $subscription = Subscription::factory()->create([
            'organization_id' => $this->organization->id,
            'billing_cycle' => 'annual',
            'next_renewal_date' => '2023-12-01',
        ]);

        $result = $this->service->calculateNextRenewal($subscription);

        $this->assertEquals('2024-12-01', $result->format('Y-m-d'));
    }

    /** @test */
    public function it_calculates_next_renewal_for_custom_cycle()
    {
        $subscription = Subscription::factory()->create([
            'organization_id' => $this->organization->id,
            'billing_cycle' => 'custom',
            'custom_days' => 15,
            'next_renewal_date' => '2023-12-01',
        ]);

        $result = $this->service->calculateNextRenewal($subscription);

        $this->assertEquals('2023-12-16', $result->format('Y-m-d'));
    }

    /** @test */
    public function it_uses_default_30_days_for_custom_cycle_without_custom_days()
    {
        $subscription = Subscription::factory()->create([
            'organization_id' => $this->organization->id,
            'billing_cycle' => 'custom',
            'custom_days' => null,
            'next_renewal_date' => '2023-12-01',
        ]);

        $result = $this->service->calculateNextRenewal($subscription);

        $this->assertEquals('2023-12-31', $result->format('Y-m-d'));
    }

    /** @test */
    public function it_defaults_to_monthly_for_unknown_cycle()
    {
        $subscription = Subscription::factory()->create([
            'organization_id' => $this->organization->id,
            'billing_cycle' => 'unknown',
            'next_renewal_date' => '2023-12-01',
        ]);

        $result = $this->service->calculateNextRenewal($subscription);

        $this->assertEquals('2024-01-01', $result->format('Y-m-d'));
    }

    /** @test */
    public function it_calculates_from_custom_date()
    {
        $subscription = Subscription::factory()->create([
            'organization_id' => $this->organization->id,
            'billing_cycle' => 'monthly',
            'next_renewal_date' => '2023-12-01',
        ]);

        $customDate = Carbon::parse('2023-11-15');
        $result = $this->service->calculateNextRenewal($subscription, $customDate);

        $this->assertEquals('2023-12-15', $result->format('Y-m-d'));
    }

    /** @test */
    public function it_updates_renewal_date_and_creates_log()
    {
        $subscription = Subscription::factory()->create([
            'organization_id' => $this->organization->id,
            'next_renewal_date' => '2023-12-01',
        ]);

        $oldDate = $subscription->next_renewal_date;
        $newDate = Carbon::parse('2024-01-01');

        $this->service->updateRenewalDate($subscription, $newDate, 'Manual update');

        $subscription->refresh();

        $this->assertEquals('2024-01-01', $subscription->next_renewal_date->format('Y-m-d'));

        $log = SubscriptionLog::where('subscription_id', $subscription->id)->first();
        $this->assertNotNull($log);
        $this->assertEquals($oldDate->format('Y-m-d'), $log->old_renewal_date->format('Y-m-d'));
        $this->assertEquals('2024-01-01', $log->new_renewal_date->format('Y-m-d'));
        $this->assertEquals('Manual update', $log->change_reason);
    }

    /** @test */
    public function it_advances_overdue_renewal_by_one_cycle()
    {
        Carbon::setTestNow('2024-01-15');

        $subscription = Subscription::factory()->create([
            'organization_id' => $this->organization->id,
            'billing_cycle' => 'monthly',
            'next_renewal_date' => '2024-01-01', // 14 days overdue
        ]);

        $cyclesAdvanced = $this->service->advanceOverdueRenewals($subscription);

        $subscription->refresh();

        $this->assertEquals(1, $cyclesAdvanced);
        $this->assertEquals('2024-02-01', $subscription->next_renewal_date->format('Y-m-d'));

        Carbon::setTestNow();
    }

    /** @test */
    public function it_advances_overdue_renewal_by_multiple_cycles()
    {
        Carbon::setTestNow('2024-03-15');

        $subscription = Subscription::factory()->create([
            'organization_id' => $this->organization->id,
            'billing_cycle' => 'monthly',
            'next_renewal_date' => '2024-01-01', // 2+ months overdue
        ]);

        $cyclesAdvanced = $this->service->advanceOverdueRenewals($subscription);

        $subscription->refresh();

        $this->assertEquals(3, $cyclesAdvanced);
        $this->assertEquals('2024-04-01', $subscription->next_renewal_date->format('Y-m-d'));

        Carbon::setTestNow();
    }

    /** @test */
    public function it_does_not_advance_current_renewal()
    {
        Carbon::setTestNow('2024-01-15');

        $subscription = Subscription::factory()->create([
            'organization_id' => $this->organization->id,
            'billing_cycle' => 'monthly',
            'next_renewal_date' => '2024-02-01', // Future date
        ]);

        $cyclesAdvanced = $this->service->advanceOverdueRenewals($subscription);

        $subscription->refresh();

        $this->assertEquals(0, $cyclesAdvanced);
        $this->assertEquals('2024-02-01', $subscription->next_renewal_date->format('Y-m-d'));

        Carbon::setTestNow();
    }

    /** @test */
    public function it_creates_log_when_advancing_overdue_renewals()
    {
        Carbon::setTestNow('2024-02-15');

        $subscription = Subscription::factory()->create([
            'organization_id' => $this->organization->id,
            'billing_cycle' => 'monthly',
            'next_renewal_date' => '2024-01-01',
        ]);

        $this->service->advanceOverdueRenewals($subscription);

        $log = SubscriptionLog::where('subscription_id', $subscription->id)->first();
        $this->assertNotNull($log);
        $this->assertStringContainsString('billing cycles', $log->change_reason);

        Carbon::setTestNow();
    }

    /** @test */
    public function it_calculates_revenue_for_period()
    {
        $subscription = Subscription::factory()->create([
            'organization_id' => $this->organization->id,
            'billing_cycle' => 'monthly',
            'price' => 100,
            'next_renewal_date' => '2024-01-15',
        ]);

        $startDate = Carbon::parse('2024-01-01');
        $endDate = Carbon::parse('2024-04-01');

        $revenue = $this->service->calculateRevenueForPeriod($subscription, $startDate, $endDate);

        // Should include renewals on: Jan 15, Feb 15, Mar 15 = 3 cycles
        $this->assertEquals(300, $revenue);
    }

    /** @test */
    public function it_returns_zero_revenue_for_zero_price()
    {
        $subscription = Subscription::factory()->create([
            'organization_id' => $this->organization->id,
            'billing_cycle' => 'monthly',
            'price' => 0,
            'next_renewal_date' => '2024-01-15',
        ]);

        $startDate = Carbon::parse('2024-01-01');
        $endDate = Carbon::parse('2024-04-01');

        $revenue = $this->service->calculateRevenueForPeriod($subscription, $startDate, $endDate);

        $this->assertEquals(0, $revenue);
    }

    /** @test */
    public function it_counts_cycles_in_period()
    {
        $subscription = Subscription::factory()->create([
            'organization_id' => $this->organization->id,
            'billing_cycle' => 'monthly',
            'next_renewal_date' => '2024-01-15',
        ]);

        $startDate = Carbon::parse('2024-01-01');
        $endDate = Carbon::parse('2024-04-01');

        $cycles = $this->service->calculateCyclesInPeriod($subscription, $startDate, $endDate);

        $this->assertEquals(3, $cycles);
    }

    /** @test */
    public function it_excludes_cycles_before_period_start()
    {
        $subscription = Subscription::factory()->create([
            'organization_id' => $this->organization->id,
            'billing_cycle' => 'monthly',
            'next_renewal_date' => '2023-12-15',
        ]);

        $startDate = Carbon::parse('2024-01-01');
        $endDate = Carbon::parse('2024-03-01');

        $cycles = $this->service->calculateCyclesInPeriod($subscription, $startDate, $endDate);

        // Should only count Jan 15 and Feb 15
        $this->assertEquals(2, $cycles);
    }

    /** @test */
    public function it_includes_renewal_on_end_date()
    {
        $subscription = Subscription::factory()->create([
            'organization_id' => $this->organization->id,
            'billing_cycle' => 'monthly',
            'next_renewal_date' => '2024-01-15',
        ]);

        $startDate = Carbon::parse('2024-01-01');
        $endDate = Carbon::parse('2024-02-15'); // Exact renewal date

        $cycles = $this->service->calculateCyclesInPeriod($subscription, $startDate, $endDate);

        $this->assertEquals(2, $cycles); // Jan 15 and Feb 15
    }
}
