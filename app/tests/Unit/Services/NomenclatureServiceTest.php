<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\NomenclatureService;
use App\Models\SettingOption;
use App\Models\User;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

class NomenclatureServiceTest extends TestCase
{
    use RefreshDatabase;

    protected NomenclatureService $service;
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

        $this->service = new NomenclatureService();
    }

    /** @test */
    public function it_retrieves_client_statuses()
    {
        SettingOption::factory()->create([
            'organization_id' => $this->organization->id,
            'category' => 'client_statuses',
            'label' => 'Active',
            'value' => 'active',
        ]);

        $result = $this->service->getClientStatuses();

        $this->assertCount(1, $result);
        $this->assertEquals('Active', $result->first()->label);
    }

    /** @test */
    public function it_caches_client_statuses()
    {
        SettingOption::factory()->create([
            'organization_id' => $this->organization->id,
            'category' => 'client_statuses',
            'label' => 'Active',
        ]);

        // First call - should hit database
        $result1 = $this->service->getClientStatuses();

        // Create new record
        SettingOption::factory()->create([
            'organization_id' => $this->organization->id,
            'category' => 'client_statuses',
            'label' => 'Inactive',
        ]);

        // Second call - should return cached result
        $result2 = $this->service->getClientStatuses();

        $this->assertCount(1, $result2); // Still 1 because cached
    }

    /** @test */
    public function it_retrieves_domain_statuses()
    {
        SettingOption::factory()->create([
            'organization_id' => $this->organization->id,
            'category' => 'domain_statuses',
            'label' => 'Active',
        ]);

        $result = $this->service->getDomainStatuses();

        $this->assertCount(1, $result);
    }

    /** @test */
    public function it_retrieves_domain_registrars()
    {
        SettingOption::factory()->create([
            'organization_id' => $this->organization->id,
            'category' => 'domain_registrars',
            'label' => 'GoDaddy',
        ]);

        $result = $this->service->getDomainRegistrars();

        $this->assertCount(1, $result);
    }

    /** @test */
    public function it_retrieves_subscription_statuses()
    {
        SettingOption::factory()->create([
            'organization_id' => $this->organization->id,
            'category' => 'subscription_statuses',
            'label' => 'Active',
        ]);

        $result = $this->service->getSubscriptionStatuses();

        $this->assertCount(1, $result);
    }

    /** @test */
    public function it_retrieves_billing_cycles()
    {
        SettingOption::factory()->create([
            'organization_id' => $this->organization->id,
            'category' => 'billing_cycles',
            'label' => 'Monthly',
        ]);

        $result = $this->service->getBillingCycles();

        $this->assertCount(1, $result);
    }

    /** @test */
    public function it_retrieves_currencies()
    {
        SettingOption::factory()->create([
            'organization_id' => $this->organization->id,
            'category' => 'currencies',
            'label' => 'RON',
        ]);

        $result = $this->service->getCurrencies();

        $this->assertCount(1, $result);
    }

    /** @test */
    public function it_retrieves_payment_methods()
    {
        SettingOption::factory()->create([
            'organization_id' => $this->organization->id,
            'category' => 'payment_methods',
            'label' => 'Cash',
        ]);

        $result = $this->service->getPaymentMethods();

        $this->assertCount(1, $result);
    }

    /** @test */
    public function it_retrieves_expense_categories_with_hierarchy()
    {
        $parent = SettingOption::factory()->create([
            'organization_id' => $this->organization->id,
            'category' => 'expense_categories',
            'parent_id' => null,
            'label' => 'Utilities',
        ]);

        SettingOption::factory()->create([
            'organization_id' => $this->organization->id,
            'category' => 'expense_categories',
            'parent_id' => $parent->id,
            'label' => 'Electricity',
        ]);

        $result = $this->service->getExpenseCategories();

        $this->assertCount(1, $result); // Only root level
        $this->assertTrue($result->first()->relationLoaded('children'));
        $this->assertCount(1, $result->first()->children);
    }

    /** @test */
    public function it_retrieves_all_expense_categories_flat()
    {
        $parent = SettingOption::factory()->create([
            'organization_id' => $this->organization->id,
            'category' => 'expense_categories',
            'parent_id' => null,
            'label' => 'Utilities',
        ]);

        SettingOption::factory()->create([
            'organization_id' => $this->organization->id,
            'category' => 'expense_categories',
            'parent_id' => $parent->id,
            'label' => 'Electricity',
        ]);

        $result = $this->service->getAllExpenseCategories();

        $this->assertCount(2, $result); // Both parent and child
    }

    /** @test */
    public function it_retrieves_all_nomenclature_at_once()
    {
        SettingOption::factory()->create([
            'organization_id' => $this->organization->id,
            'category' => 'client_statuses',
        ]);
        SettingOption::factory()->create([
            'organization_id' => $this->organization->id,
            'category' => 'domain_statuses',
        ]);

        $result = $this->service->getAll();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('client_statuses', $result);
        $this->assertArrayHasKey('domain_statuses', $result);
        $this->assertArrayHasKey('expense_categories', $result);
    }

    /** @test */
    public function it_calculates_nomenclature_counts()
    {
        SettingOption::factory()->count(3)->create([
            'organization_id' => $this->organization->id,
            'category' => 'client_statuses',
        ]);
        SettingOption::factory()->count(2)->create([
            'organization_id' => $this->organization->id,
            'category' => 'domain_statuses',
        ]);

        $result = $this->service->getCounts();

        $this->assertIsArray($result);
        $this->assertEquals(3, $result['client_statuses']);
        $this->assertEquals(2, $result['domain_statuses']);
    }

    /** @test */
    public function it_clears_all_caches()
    {
        SettingOption::factory()->create([
            'organization_id' => $this->organization->id,
            'category' => 'client_statuses',
            'label' => 'Active',
        ]);

        // Prime the cache
        $this->service->getClientStatuses();

        // Add new record
        SettingOption::factory()->create([
            'organization_id' => $this->organization->id,
            'category' => 'client_statuses',
            'label' => 'Inactive',
        ]);

        // Should still return cached (1 item)
        $this->assertCount(1, $this->service->getClientStatuses());

        // Clear cache
        $this->service->clearCache();

        // Should now return fresh data (2 items)
        $this->assertCount(2, $this->service->getClientStatuses());
    }

    /** @test */
    public function it_clears_specific_category_cache()
    {
        SettingOption::factory()->create([
            'organization_id' => $this->organization->id,
            'category' => 'client_statuses',
        ]);
        SettingOption::factory()->create([
            'organization_id' => $this->organization->id,
            'category' => 'domain_statuses',
        ]);

        // Prime both caches
        $this->service->getClientStatuses();
        $this->service->getDomainStatuses();

        // Add new records
        SettingOption::factory()->create([
            'organization_id' => $this->organization->id,
            'category' => 'client_statuses',
        ]);
        SettingOption::factory()->create([
            'organization_id' => $this->organization->id,
            'category' => 'domain_statuses',
        ]);

        // Clear only client_statuses
        $this->service->clearCacheFor('client_statuses');

        // Client statuses should be refreshed (2 items)
        $this->assertCount(2, $this->service->getClientStatuses());

        // Domain statuses should still be cached (1 item)
        $this->assertCount(1, $this->service->getDomainStatuses());
    }

    /** @test */
    public function it_uses_organization_specific_cache_keys()
    {
        // Create another organization and user
        $org2 = Organization::factory()->create();
        $user2 = User::factory()->create(['organization_id' => $org2->id]);

        // Create setting for org1
        SettingOption::factory()->create([
            'organization_id' => $this->organization->id,
            'category' => 'client_statuses',
            'label' => 'Org1 Status',
        ]);

        // Create setting for org2
        SettingOption::factory()->create([
            'organization_id' => $org2->id,
            'category' => 'client_statuses',
            'label' => 'Org2 Status',
        ]);

        // Get data as org1 user
        $result1 = $this->service->getClientStatuses();

        // Switch to org2 user
        $this->actingAs($user2);
        $result2 = $this->service->getClientStatuses();

        // Should return different data
        $this->assertCount(1, $result1);
        $this->assertCount(1, $result2);
        $this->assertEquals('Org1 Status', $result1->first()->label);
        $this->assertEquals('Org2 Status', $result2->first()->label);
    }
}
