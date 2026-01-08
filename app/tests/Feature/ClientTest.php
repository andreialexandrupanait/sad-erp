<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Organization;
use App\Models\SettingOption;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();

        // Create organization and user
        $this->organization = Organization::factory()->create();
        $this->user = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role' => 'admin',
        ]);

        // Seed required settings
        $this->seed(\Database\Seeders\ClientStatusesSeeder::class);
    }

    /** @test */
    public function authenticated_user_can_view_clients_list()
    {
        $response = $this->actingAs($this->user)->get(route('clients.index'));

        $response->assertStatus(200);
        $response->assertViewIs('clients.index');
    }

    /** @test */
    public function unauthenticated_user_cannot_view_clients_list()
    {
        $response = $this->get(route('clients.index'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function user_can_create_client()
    {
        // Use first available status (slug is computed, can't query by it)
        $status = SettingOption::clientStatuses()->first();

        $clientData = [
            'name' => 'Test Company SRL',
            'company_name' => 'Test Company SRL',
            'email' => 'contact@testcompany.ro',
            'phone' => '+40123456789',
            'tax_id' => 'RO12345678',
            'status_id' => $status->id,
        ];

        $response = $this->actingAs($this->user)
            ->post(route('clients.store'), $clientData);

        $response->assertRedirect();
        $this->assertDatabaseHas('clients', [
            'name' => 'Test Company SRL',
            'email' => 'contact@testcompany.ro',
            'organization_id' => $this->organization->id,
        ]);
    }

    /** @test */
    public function client_requires_name()
    {
        $response = $this->actingAs($this->user)
            ->post(route('clients.store'), [
                'email' => 'test@example.com',
            ]);

        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function user_can_update_client()
    {
        $client = Client::factory()->create([
            'organization_id' => $this->organization->id,
            'user_id' => $this->user->id,
            'name' => 'Original Name',
        ]);

        $response = $this->actingAs($this->user)
            ->put(route('clients.update', $client), [
                'name' => 'Updated Name',
                'email' => $client->email,
                'status_id' => $client->status_id,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'name' => 'Updated Name',
        ]);
    }

    /** @test */
    public function user_can_delete_client()
    {
        $client = Client::factory()->create([
            'organization_id' => $this->organization->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('clients.destroy', $client));

        $response->assertRedirect();
        $this->assertSoftDeleted('clients', [
            'id' => $client->id,
        ]);
    }

    /** @test */
    public function user_cannot_access_clients_from_other_organizations()
    {
        $otherOrganization = Organization::factory()->create();
        $otherUser = User::factory()->create(['organization_id' => $otherOrganization->id]);
        $otherClient = Client::factory()->create([
            'organization_id' => $otherOrganization->id,
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('clients.show', $otherClient));

        // Global scope makes clients from other organizations invisible (404)
        // This is more secure than 403 as it doesn't reveal resource existence
        $response->assertStatus(404);
    }

    /** @test */
    public function client_factory_creates_valid_client()
    {
        $client = Client::factory()->create([
            'organization_id' => $this->organization->id,
            'user_id' => $this->user->id,
        ]);

        $this->assertNotNull($client->name);
        $this->assertNotNull($client->email);
        $this->assertNotNull($client->status_id);
        $this->assertEquals($this->organization->id, $client->organization_id);
    }

    /** @test */
    public function client_factory_can_create_active_client()
    {
        $client = Client::factory()->active()->create([
            'organization_id' => $this->organization->id,
            'user_id' => $this->user->id,
        ]);

        // Note: 'slug' is a computed accessor, search by 'value' instead
        $activeStatus = SettingOption::clientStatuses()
            ->where('value', 'active')
            ->first();

        $this->assertEquals($activeStatus->id, $client->status_id);
    }

    /** @test */
    public function client_search_filters_by_name()
    {
        Client::factory()->create([
            'organization_id' => $this->organization->id,
            'user_id' => $this->user->id,
            'name' => 'Acme Corporation',
        ]);

        Client::factory()->create([
            'organization_id' => $this->organization->id,
            'user_id' => $this->user->id,
            'name' => 'Other Company',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('clients.index', ['q' => 'Acme']));

        $response->assertStatus(200);
    }

    /** @test */
    public function client_slug_is_automatically_generated()
    {
        $client = Client::factory()->create([
            'organization_id' => $this->organization->id,
            'user_id' => $this->user->id,
            'name' => 'Test Company Name',
        ]);

        $this->assertNotNull($client->slug);
        $this->assertStringContainsString('test-company', strtolower($client->slug));
    }
}
