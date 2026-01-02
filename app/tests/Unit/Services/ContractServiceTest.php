<?php

namespace Tests\Unit\Services;

use App\Models\Client;
use App\Models\Contract;
use App\Models\ContractTemplate;
use App\Models\Offer;
use App\Models\OfferItem;
use App\Models\Organization;
use App\Models\User;
use App\Services\Contract\ContractService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test suite for ContractService
 *
 * @covers \App\Services\Contract\ContractService
 */
class ContractServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ContractService $service;
    protected User $user;
    protected Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new ContractService();

        // Create test organization and user
        $this->organization = Organization::factory()->create();
        $this->user = User::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $this->actingAs($this->user);
    }

    /** @test */
    public function it_throws_exception_when_creating_from_non_accepted_offer(): void
    {
        $offer = Offer::factory()->draft()->create([
            'organization_id' => $this->organization->id,
        ]);

        $this->expectException(\RuntimeException::class);

        $this->service->createFromOffer($offer);
    }

    /** @test */
    public function it_creates_contract_from_accepted_offer(): void
    {
        $client = Client::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $offer = Offer::factory()->accepted()->create([
            'organization_id' => $this->organization->id,
            'client_id' => $client->id,
            'total' => 5000.00,
            'currency' => 'EUR',
        ]);

        $contract = $this->service->createFromOffer($offer);

        $this->assertInstanceOf(Contract::class, $contract);
        $this->assertEquals($this->organization->id, $contract->organization_id);
        $this->assertEquals($client->id, $contract->client_id);
        $this->assertEquals($offer->id, $contract->offer_id);
        $this->assertEquals(5000.00, $contract->total_value);
        $this->assertEquals('EUR', $contract->currency);
        $this->assertEquals('active', $contract->status);

        // Verify offer is linked to contract
        $offer->refresh();
        $this->assertEquals($contract->id, $offer->contract_id);
    }

    /** @test */
    public function it_terminates_active_contract(): void
    {
        $contract = Contract::factory()->active()->create([
            'organization_id' => $this->organization->id,
        ]);

        $this->service->terminate($contract, 'Client requested termination');

        $contract->refresh();
        $this->assertEquals('terminated', $contract->status);
        $this->assertEquals('Client requested termination', $contract->termination_reason);
    }

    /** @test */
    public function it_throws_exception_when_terminating_inactive_contract(): void
    {
        $contract = Contract::factory()->draft()->create([
            'organization_id' => $this->organization->id,
        ]);

        $this->expectException(\RuntimeException::class);

        $this->service->terminate($contract);
    }

    /** @test */
    public function it_renews_contract_creating_new_one(): void
    {
        $contract = Contract::factory()->active()->create([
            'organization_id' => $this->organization->id,
            'total_value' => 10000,
            'currency' => 'EUR',
        ]);

        $newContract = $this->service->renew($contract, [
            'total_value' => 12000,
        ]);

        // Old contract should be expired
        $contract->refresh();
        $this->assertEquals('expired', $contract->status);

        // New contract should be active
        $this->assertInstanceOf(Contract::class, $newContract);
        $this->assertEquals('active', $newContract->status);
        $this->assertEquals($contract->id, $newContract->parent_contract_id);
        $this->assertEquals(12000, $newContract->total_value);
        $this->assertEquals($contract->client_id, $newContract->client_id);
    }

    /** @test */
    public function it_gets_expiring_contracts_within_days(): void
    {
        // Create contracts expiring at different times
        Contract::factory()->active()->create([
            'organization_id' => $this->organization->id,
            'end_date' => now()->addDays(10),
        ]);

        Contract::factory()->active()->create([
            'organization_id' => $this->organization->id,
            'end_date' => now()->addDays(25),
        ]);

        Contract::factory()->active()->create([
            'organization_id' => $this->organization->id,
            'end_date' => now()->addDays(45),
        ]);

        // Also create an indefinite contract (should not appear)
        Contract::factory()->active()->indefinite()->create([
            'organization_id' => $this->organization->id,
        ]);

        $expiring = $this->service->getExpiringContracts(30, $this->organization->id);

        $this->assertCount(2, $expiring);
    }

    /** @test */
    public function it_creates_draft_from_offer_with_template(): void
    {
        $client = Client::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $template = ContractTemplate::factory()->create([
            'organization_id' => $this->organization->id,
            'is_default' => true,
            'is_active' => true,
            'content' => '<p>Contract for {{client_name}}</p>',
        ]);

        $offer = Offer::factory()->accepted()->create([
            'organization_id' => $this->organization->id,
            'client_id' => $client->id,
            'title' => 'Test Offer',
            'total' => 3000.00,
        ]);

        // Add offer items
        OfferItem::factory()->count(2)->create([
            'offer_id' => $offer->id,
            'is_selected' => true,
            'unit_price' => 1500,
            'quantity' => 1,
        ]);

        $contract = $this->service->createDraftFromOffer($offer, $template);

        $this->assertEquals('draft', $contract->status);
        $this->assertEquals($template->id, $contract->contract_template_id);
        $this->assertEquals($client->id, $contract->client_id);

        // Contract items should be created from selected offer items
        $this->assertEquals(2, $contract->items()->count());
    }

    /** @test */
    public function it_creates_client_from_temp_data_when_creating_draft(): void
    {
        $offer = Offer::factory()->accepted()->withTempClient()->create([
            'organization_id' => $this->organization->id,
            'temp_client_name' => 'John Doe',
            'temp_client_email' => 'john@example.com',
            'temp_client_company' => 'Doe Industries',
        ]);

        $contract = $this->service->createDraftFromOffer($offer);

        // Client should have been created
        $this->assertNotNull($contract->client_id);
        $this->assertEquals('John Doe', $contract->client->name);
        $this->assertEquals('john@example.com', $contract->client->email);

        // Offer should be linked to the new client
        $offer->refresh();
        $this->assertEquals($contract->client_id, $offer->client_id);
    }

    /** @test */
    public function it_throws_exception_when_adding_annex_from_non_accepted_offer(): void
    {
        $contract = Contract::factory()->active()->create([
            'organization_id' => $this->organization->id,
        ]);

        $offer = Offer::factory()->sent()->create([
            'organization_id' => $this->organization->id,
        ]);

        $this->expectException(\RuntimeException::class);

        $this->service->addAnnexFromOffer($contract, $offer);
    }

    /** @test */
    public function it_adds_annex_from_accepted_offer(): void
    {
        $contract = Contract::factory()->active()->create([
            'organization_id' => $this->organization->id,
            'total_value' => 10000,
        ]);

        $offer = Offer::factory()->accepted()->create([
            'organization_id' => $this->organization->id,
            'total' => 2000.00,
        ]);

        $annex = $this->service->addAnnexFromOffer($contract, $offer);

        $this->assertEquals($contract->id, $annex->contract_id);
        $this->assertEquals($offer->id, $annex->offer_id);
        $this->assertEquals(1, $annex->annex_number);
        $this->assertEquals(2000.00, $annex->value);

        // Contract value should be updated
        $contract->refresh();
        $this->assertEquals(12000, $contract->total_value);

        // Offer should be linked to contract
        $offer->refresh();
        $this->assertEquals($contract->id, $offer->contract_id);
    }

    /** @test */
    public function it_gets_contract_statistics(): void
    {
        // Create contracts with various statuses
        Contract::factory()->active()->count(3)->create([
            'organization_id' => $this->organization->id,
            'total_value' => 1000,
        ]);

        Contract::factory()->expired()->count(2)->create([
            'organization_id' => $this->organization->id,
        ]);

        Contract::factory()->terminated()->create([
            'organization_id' => $this->organization->id,
        ]);

        $stats = $this->service->getStatistics($this->organization->id);

        $this->assertEquals(6, $stats['total']);
        $this->assertEquals(3, $stats['active']);
        $this->assertEquals(2, $stats['expired']);
        $this->assertEquals(1, $stats['terminated']);
        $this->assertEquals(3000, $stats['active_value']);
    }

    /** @test */
    public function it_renders_template_for_contract_with_variables(): void
    {
        $client = Client::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Test Client',
            'company_name' => 'Test Company',
        ]);

        $contract = Contract::factory()->active()->create([
            'organization_id' => $this->organization->id,
            'client_id' => $client->id,
            'contract_number' => 'CTR-2025-01',
            'total_value' => 5000,
            'currency' => 'EUR',
        ]);

        $template = ContractTemplate::factory()->create([
            'organization_id' => $this->organization->id,
            'content' => '<p>Contract: {{contract_number}}</p><p>Client: {{client_name}}</p><p>Value: {{contract_total}} {{currency}}</p>',
        ]);

        $rendered = $this->service->renderTemplateForContract($contract, $template);

        $this->assertStringContainsString('CTR-2025-01', $rendered);
        $this->assertStringContainsString('Test Client', $rendered);
        $this->assertStringContainsString('EUR', $rendered);
    }
}
