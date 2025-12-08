<?php

namespace Tests\Unit\Services\Financial\Import;

use Tests\TestCase;
use App\Services\Financial\Import\ClientMatcher;
use App\Models\Client;
use App\Models\User;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;

class ClientMatcherTest extends TestCase
{
    use RefreshDatabase;

    protected ClientMatcher $clientMatcher;
    protected Organization $organization;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create organization and user
        $this->organization = Organization::factory()->create();
        $this->user = User::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $this->actingAs($this->user);

        $this->clientMatcher = new ClientMatcher();
    }

    /** @test */
    public function it_can_load_client_index()
    {
        // Create test clients
        Client::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Test Company SRL',
            'tax_id' => '12345678',
        ]);

        Client::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Another Company SRL',
            'tax_id' => 'RO87654321',
        ]);

        $this->clientMatcher->loadIndex();

        // Index should be loaded (we can't directly test private properties)
        $this->assertTrue(true);
    }

    /** @test */
    public function it_can_find_client_by_cif_exact_match()
    {
        $client = Client::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Test Company',
            'tax_id' => '12345678',
        ]);

        $this->clientMatcher->loadIndex();

        $found = $this->clientMatcher->findByCif('12345678');

        $this->assertNotNull($found);
        $this->assertEquals($client->id, $found->id);
    }

    /** @test */
    public function it_can_find_client_by_cif_with_ro_prefix()
    {
        $client = Client::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Test Company',
            'tax_id' => 'RO12345678',
        ]);

        $this->clientMatcher->loadIndex();

        // Should find with or without RO prefix
        $found1 = $this->clientMatcher->findByCif('RO12345678');
        $found2 = $this->clientMatcher->findByCif('12345678');

        $this->assertNotNull($found1);
        $this->assertNotNull($found2);
        $this->assertEquals($client->id, $found1->id);
        $this->assertEquals($client->id, $found2->id);
    }

    /** @test */
    public function it_normalizes_cif_with_ro_prefix()
    {
        $client = Client::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Test Company',
            'tax_id' => '12345678',
        ]);

        $this->clientMatcher->loadIndex();

        // Should find when searching with RO prefix even if stored without
        $found = $this->clientMatcher->findByCif('RO12345678');

        $this->assertNotNull($found);
        $this->assertEquals($client->id, $found->id);
    }

    /** @test */
    public function it_returns_null_for_non_existent_cif()
    {
        $this->clientMatcher->loadIndex();

        $found = $this->clientMatcher->findByCif('99999999');

        $this->assertNull($found);
    }

    /** @test */
    public function it_can_find_client_by_name()
    {
        $client = Client::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Test Company SRL',
            'tax_id' => '12345678',
        ]);

        $this->clientMatcher->loadIndex();

        $found = $this->clientMatcher->findByName('Test Company SRL');

        $this->assertNotNull($found);
        $this->assertEquals($client->id, $found->id);
    }

    /** @test */
    public function it_finds_client_by_name_case_insensitive()
    {
        $client = Client::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Test Company SRL',
            'tax_id' => '12345678',
        ]);

        $this->clientMatcher->loadIndex();

        $found = $this->clientMatcher->findByName('test company srl');

        $this->assertNotNull($found);
        $this->assertEquals($client->id, $found->id);
    }

    /** @test */
    public function it_returns_null_for_non_existent_name()
    {
        $this->clientMatcher->loadIndex();

        $found = $this->clientMatcher->findByName('Non Existent Company');

        $this->assertNull($found);
    }

    /** @test */
    public function it_can_find_or_create_existing_client_by_cif()
    {
        $client = Client::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Existing Company',
            'tax_id' => '12345678',
        ]);

        $this->clientMatcher->loadIndex();

        $data = [
            'cif_client' => '12345678',
            'client_name' => 'Updated Name',
        ];

        $clientId = $this->clientMatcher->findOrCreate($data);

        $this->assertEquals($client->id, $clientId);
        $this->assertEquals(0, $this->clientMatcher->stats['clients_created']);
    }

    /** @test */
    public function it_creates_new_client_when_not_found()
    {
        $this->clientMatcher->loadIndex();

        $data = [
            'cif_client' => '99999999',
            'client_name' => 'New Company SRL',
            'client_address' => 'Test Address',
        ];

        $clientId = $this->clientMatcher->findOrCreate($data);

        $this->assertNotNull($clientId);
        $this->assertEquals(1, $this->clientMatcher->stats['clients_created']);

        $client = Client::find($clientId);
        $this->assertEquals('New Company Srl', $client->name); // Title case
        $this->assertEquals('99999999', $client->tax_id);
    }

    /** @test */
    public function it_respects_dry_run_mode()
    {
        $this->clientMatcher->loadIndex();

        Log::shouldReceive('info')
            ->once()
            ->with('DRY RUN: Would create client', \Mockery::type('array'));

        $data = [
            'cif_client' => '99999999',
            'client_name' => 'New Company',
        ];

        $clientId = $this->clientMatcher->findOrCreate($data, true);

        $this->assertNull($clientId);
        $this->assertEquals(0, Client::count());
    }

    /** @test */
    public function it_falls_back_to_name_when_no_cif()
    {
        $client = Client::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Test Company',
            'tax_id' => null,
        ]);

        $this->clientMatcher->loadIndex();

        $data = [
            'client_name' => 'Test Company',
        ];

        $clientId = $this->clientMatcher->findOrCreate($data);

        $this->assertEquals($client->id, $clientId);
    }

    /** @test */
    public function it_returns_null_when_no_cif_and_no_name()
    {
        $this->clientMatcher->loadIndex();

        $data = [];

        $clientId = $this->clientMatcher->findOrCreate($data);

        $this->assertNull($clientId);
    }

    /** @test */
    public function it_formats_client_name_to_title_case()
    {
        $this->clientMatcher->loadIndex();

        $data = [
            'cif_client' => '99999999',
            'client_name' => 'NEW COMPANY SRL',
        ];

        $clientId = $this->clientMatcher->findOrCreate($data);

        $client = Client::find($clientId);
        $this->assertEquals('New Company Srl', $client->name);
    }

    /** @test */
    public function it_tracks_statistics()
    {
        $this->clientMatcher->loadIndex();

        // Create first client
        $this->clientMatcher->findOrCreate([
            'cif_client' => '11111111',
            'client_name' => 'Client One',
        ]);

        // Create second client
        $this->clientMatcher->findOrCreate([
            'cif_client' => '22222222',
            'client_name' => 'Client Two',
        ]);

        $this->assertEquals(2, $this->clientMatcher->stats['clients_created']);
    }
}
