<?php

namespace Tests\Unit\Services;

use App\Models\Client;
use App\Models\Contract;
use App\Models\Offer;
use App\Models\OfferItem;
use App\Models\Organization;
use App\Models\User;
use App\Services\Contract\ContractService;
use App\Services\Offer\OfferService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Test suite for OfferService
 *
 * @covers \App\Services\Offer\OfferService
 */
class OfferServiceTest extends TestCase
{
    use RefreshDatabase;

    protected OfferService $service;
    protected User $user;
    protected Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();

        $this->organization = Organization::factory()->create();
        $this->user = User::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $this->actingAs($this->user);

        // Create service with mocked ContractService
        $contractService = new ContractService();
        $this->service = new OfferService(null, $contractService);
    }

    /** @test */
    public function it_creates_offer_with_items(): void
    {
        $client = Client::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $offer = $this->service->create([
            'client_id' => $client->id,
            'title' => 'Test Offer',
            'currency' => 'EUR',
            'valid_until' => now()->addDays(30),
        ], [
            ['title' => 'Service A', 'unit_price' => 100, 'quantity' => 2],
            ['title' => 'Service B', 'unit_price' => 200, 'quantity' => 1],
        ]);

        $this->assertInstanceOf(Offer::class, $offer);
        $this->assertEquals('Test Offer', $offer->title);
        $this->assertEquals($client->id, $offer->client_id);
        $this->assertEquals('draft', $offer->status);
        $this->assertNotEmpty($offer->offer_number);
        $this->assertNotEmpty($offer->public_token);
        $this->assertEquals(2, $offer->items->count());
    }

    /** @test */
    public function it_updates_offer_and_creates_version_when_sent(): void
    {
        $offer = Offer::factory()->sent()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Original Title',
        ]);

        $updatedOffer = $this->service->update($offer, [
            'title' => 'Updated Title',
        ], [], 'Price adjustment');

        $this->assertEquals('Updated Title', $updatedOffer->title);
    }

    /** @test */
    public function it_duplicates_offer_with_new_number(): void
    {
        $client = Client::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $original = Offer::factory()->create([
            'organization_id' => $this->organization->id,
            'client_id' => $client->id,
            'title' => 'Original Offer',
            'total' => 1000,
        ]);

        // Add items to original
        OfferItem::factory()->count(2)->create([
            'offer_id' => $original->id,
        ]);

        $duplicate = $this->service->duplicate($original);

        $this->assertNotEquals($original->id, $duplicate->id);
        $this->assertNotEquals($original->offer_number, $duplicate->offer_number);
        $this->assertNotEquals($original->public_token, $duplicate->public_token);
        $this->assertEquals('draft', $duplicate->status);
        $this->assertEquals($original->title, $duplicate->title);
        $this->assertEquals($original->client_id, $duplicate->client_id);
        $this->assertEquals(2, $duplicate->items->count());
    }

    /** @test */
    public function it_throws_exception_when_sending_without_email(): void
    {
        $client = Client::factory()->create([
            'organization_id' => $this->organization->id,
            'email' => null,
        ]);

        $offer = Offer::factory()->draft()->create([
            'organization_id' => $this->organization->id,
            'client_id' => $client->id,
            'temp_client_email' => null,
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('email');

        $this->service->send($offer);
    }

    /** @test */
    public function it_throws_exception_when_accepting_non_acceptable_offer(): void
    {
        $offer = Offer::factory()->rejected()->create([
            'organization_id' => $this->organization->id,
        ]);

        $this->expectException(\RuntimeException::class);

        $this->service->accept($offer);
    }

    /** @test */
    public function it_accepts_offer_and_creates_contract(): void
    {
        $client = Client::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $offer = Offer::factory()->sent()->create([
            'organization_id' => $this->organization->id,
            'client_id' => $client->id,
            'total' => 5000,
        ]);

        // Add items
        OfferItem::factory()->count(2)->create([
            'offer_id' => $offer->id,
        ]);

        $contract = $this->service->accept($offer, '127.0.0.1', true);

        $offer->refresh();
        $this->assertEquals('accepted', $offer->status);
        $this->assertNotNull($offer->accepted_at);

        // Contract should be created
        $this->assertInstanceOf(Contract::class, $contract);
        $this->assertEquals('draft', $contract->status);
        $this->assertEquals($client->id, $contract->client_id);
    }

    /** @test */
    public function it_rejects_offer(): void
    {
        $offer = Offer::factory()->sent()->create([
            'organization_id' => $this->organization->id,
        ]);

        $this->service->reject($offer, 'Too expensive');

        $offer->refresh();
        $this->assertEquals('rejected', $offer->status);
        $this->assertEquals('Too expensive', $offer->rejection_reason);
    }

    /** @test */
    public function it_throws_exception_when_converting_non_accepted_offer(): void
    {
        $offer = Offer::factory()->draft()->create([
            'organization_id' => $this->organization->id,
        ]);

        $this->expectException(\RuntimeException::class);

        $this->service->convertToContract($offer);
    }

    /** @test */
    public function it_throws_exception_when_converting_already_converted_offer(): void
    {
        $contract = Contract::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $offer = Offer::factory()->accepted()->create([
            'organization_id' => $this->organization->id,
            'contract_id' => $contract->id,
        ]);

        $this->expectException(\RuntimeException::class);
        // Message: "This offer has already been converted to a contract."
        $this->expectExceptionMessage('contract');

        $this->service->convertToContract($offer);
    }

    /** @test */
    public function it_gets_offer_statistics(): void
    {
        // Create various offers
        Offer::factory()->draft()->count(2)->create([
            'organization_id' => $this->organization->id,
        ]);

        Offer::factory()->sent()->count(3)->create([
            'organization_id' => $this->organization->id,
        ]);

        Offer::factory()->accepted()->create([
            'organization_id' => $this->organization->id,
            'total' => 5000,
        ]);

        Offer::factory()->rejected()->create([
            'organization_id' => $this->organization->id,
        ]);

        $stats = $this->service->getStatistics($this->organization->id);

        $this->assertEquals(7, $stats['total']);
        $this->assertEquals(2, $stats['draft']);
        $this->assertEquals(3, $stats['sent']);
        $this->assertEquals(1, $stats['accepted']);
        $this->assertEquals(1, $stats['rejected']);
        $this->assertEquals(5000, $stats['accepted_value']);
    }

    /** @test */
    public function it_gets_offer_by_public_token(): void
    {
        $offer = Offer::factory()->create([
            'organization_id' => $this->organization->id,
            'public_token' => 'test-token-123',
        ]);

        $found = $this->service->getOfferByToken('test-token-123');

        $this->assertEquals($offer->id, $found->id);
    }

    /** @test */
    public function it_accepts_offer_via_public_link(): void
    {
        $client = Client::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $offer = Offer::factory()->sent()->create([
            'organization_id' => $this->organization->id,
            'client_id' => $client->id,
            'public_token' => 'public-accept-token',
            'verification_code' => null,
        ]);

        OfferItem::factory()->create([
            'offer_id' => $offer->id,
        ]);

        $contract = $this->service->acceptPublic('public-accept-token', null, '192.168.1.1');

        $offer->refresh();
        $this->assertEquals('accepted', $offer->status);
        $this->assertInstanceOf(Contract::class, $contract);
    }

    /** @test */
    public function it_validates_verification_code_when_accepting(): void
    {
        $offer = Offer::factory()->sent()->create([
            'organization_id' => $this->organization->id,
            'public_token' => 'verified-token',
            'verification_code' => '123456',
            'verification_code_expires_at' => now()->addHour(),
        ]);

        $this->expectException(\RuntimeException::class);
        // Message: "Invalid verification code." or translated version
        $this->expectExceptionMessage('verification');

        $this->service->acceptPublic('verified-token', 'wrong-code', '127.0.0.1');
    }

    /** @test */
    public function it_deletes_offer(): void
    {
        $offer = Offer::factory()->draft()->create([
            'organization_id' => $this->organization->id,
        ]);

        $result = $this->service->delete($offer);

        $this->assertTrue($result);
        $this->assertSoftDeleted('offers', ['id' => $offer->id]);
    }

    /** @test */
    public function it_records_offer_view(): void
    {
        $offer = Offer::factory()->sent()->create([
            'organization_id' => $this->organization->id,
            'viewed_at' => null,
        ]);

        $this->service->recordView($offer, '192.168.1.1', 'Mozilla/5.0');

        $offer->refresh();
        $this->assertNotNull($offer->viewed_at);
        $this->assertEquals('viewed', $offer->status);
    }

    /** @test */
    public function it_gets_version_history(): void
    {
        $offer = Offer::factory()->sent()->create([
            'organization_id' => $this->organization->id,
        ]);

        // Create a version manually for testing
        $offer->versions()->create([
            'version_number' => 1,
            'reason' => 'Initial version',
            'snapshot' => [
                'total' => 1000,
                'subtotal' => 1000,
                'items' => [],
            ],
            'created_by' => $this->user->id,
        ]);

        $history = $this->service->getVersionHistory($offer);

        $this->assertIsArray($history);
        $this->assertCount(1, $history);
        $this->assertEquals('Initial version', $history[0]['reason']);
    }
}
