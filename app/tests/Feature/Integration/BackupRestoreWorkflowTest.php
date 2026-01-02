<?php

namespace Tests\Feature\Integration;

use Tests\TestCase;
use App\Models\User;
use App\Models\Organization;
use App\Models\FinancialRevenue;
use App\Models\Client;
use App\Services\Database\DatabaseBackupService;
use App\Services\Database\DatabaseRestoreService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

class BackupRestoreWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Organization $organization;
    protected DatabaseBackupService $backupService;
    protected DatabaseRestoreService $restoreService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organization = Organization::factory()->create();
        $this->admin = User::factory()->create([
            'role' => 'admin',
            'organization_id' => $this->organization->id,
        ]);

        $this->backupService = app(DatabaseBackupService::class);
        $this->restoreService = app(DatabaseRestoreService::class);
    }

    /**
     * Test complete backup and restore workflow
     *
     * @test
     */
    public function complete_backup_and_restore_workflow()
    {
        // 1. Create test data
        $client = Client::factory()->create([
            'organization_id' => $this->organization->id,
            'user_id' => $this->admin->id,
            'name' => 'Test Client Inc',
        ]);

        $revenue = FinancialRevenue::factory()->create([
            'organization_id' => $this->organization->id,
            'client_id' => $client->id,
            'amount' => 5000.00,
            'currency' => 'EUR',
            'year' => 2025,
            'month' => 12,
        ]);

        // 2. Create backup
        $backupResult = $this->backupService->createBackup(['financial_revenues', 'clients']);

        $this->assertTrue($backupResult['success']);
        $this->assertNotEmpty($backupResult['filename']);
        $this->assertEquals(2, $backupResult['tables_count']);

        // Verify backup file exists
        $this->assertTrue(Storage::disk('local')->exists('backups/' . $backupResult['filename']));

        // 3. Modify data (simulate data loss)
        $revenue->update(['amount' => 999.99]);
        $client->update(['name' => 'Modified Client']);

        // Verify data was changed
        $this->assertDatabaseHas('financial_revenues', [
            'id' => $revenue->id,
            'amount' => 999.99,
        ]);

        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'name' => 'Modified Client',
        ]);

        // 4. Restore from backup (merge mode)
        $restoreResult = $this->restoreService->restoreFromBackup(
            $backupResult['filename'],
            'merge'
        );

        $this->assertTrue($restoreResult['success']);
        $this->assertArrayHasKey('financial_revenues', $restoreResult['imported']);
        $this->assertArrayHasKey('clients', $restoreResult['imported']);
        $this->assertEmpty($restoreResult['errors']);

        // 5. Verify data was restored
        $this->assertDatabaseHas('financial_revenues', [
            'id' => $revenue->id,
            'amount' => 5000.00, // Original value restored
        ]);

        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'name' => 'Test Client Inc', // Original value restored
        ]);

        // 6. Cleanup
        Storage::disk('local')->delete('backups/' . $backupResult['filename']);
    }

    /**
     * Test backup preserves all data integrity
     *
     * @test
     */
    public function backup_preserves_data_integrity()
    {
        // Create complex data with relationships
        $client = Client::factory()->create([
            'organization_id' => $this->organization->id,
            'user_id' => $this->admin->id,
        ]);

        $revenues = FinancialRevenue::factory()->count(3)->create([
            'organization_id' => $this->organization->id,
            'client_id' => $client->id,
        ]);

        // Create backup
        $backupResult = $this->backupService->createBackup(['clients', 'financial_revenues']);

        // Read backup file
        $backupContent = Storage::disk('local')->get('backups/' . $backupResult['filename']);
        $backupData = json_decode($backupContent, true);

        // Verify structure
        $this->assertArrayHasKey('meta', $backupData);
        $this->assertArrayHasKey('data', $backupData);
        $this->assertArrayHasKey('clients', $backupData['data']);
        $this->assertArrayHasKey('financial_revenues', $backupData['data']);

        // Verify client count
        $this->assertCount(1, $backupData['data']['clients']);

        // Verify revenues count
        $this->assertCount(3, $backupData['data']['financial_revenues']);

        // Verify data matches
        $backupClient = $backupData['data']['clients'][0];
        $this->assertEquals($client->id, $backupClient['id']);
        $this->assertEquals($client->name, $backupClient['name']);

        // Cleanup
        Storage::disk('local')->delete('backups/' . $backupResult['filename']);
    }

    /**
     * Test replace mode completely replaces data
     *
     * @test
     */
    public function replace_mode_completely_replaces_table_data()
    {
        // Create initial data
        $client1 = Client::factory()->create([
            'organization_id' => $this->organization->id,
            'user_id' => $this->admin->id,
            'name' => 'Original Client 1',
        ]);

        $client2 = Client::factory()->create([
            'organization_id' => $this->organization->id,
            'user_id' => $this->admin->id,
            'name' => 'Original Client 2',
        ]);

        // Create backup with only client1
        $backupResult = $this->backupService->createBackup(['clients']);

        // Delete client1, keep client2
        $client1->delete();

        // Verify only client2 exists
        $this->assertEquals(1, Client::count());
        $this->assertDatabaseMissing('clients', ['id' => $client1->id]);
        $this->assertDatabaseHas('clients', ['id' => $client2->id]);

        // Restore with replace mode
        $restoreResult = $this->restoreService->restoreFromBackup(
            $backupResult['filename'],
            'replace'
        );

        $this->assertTrue($restoreResult['success']);

        // Verify client1 is back, client2 is gone (replaced)
        $this->assertEquals(1, Client::count());
        $this->assertDatabaseHas('clients', ['id' => $client1->id]);
        $this->assertDatabaseMissing('clients', ['id' => $client2->id]);

        // Cleanup
        Storage::disk('local')->delete('backups/' . $backupResult['filename']);
    }

    /**
     * Test merge mode preserves existing data
     *
     * @test
     */
    public function merge_mode_preserves_existing_data()
    {
        $client1 = Client::factory()->create([
            'organization_id' => $this->organization->id,
            'user_id' => $this->admin->id,
        ]);

        // Create backup
        $backupResult = $this->backupService->createBackup(['clients']);

        // Create new client after backup
        $client2 = Client::factory()->create([
            'organization_id' => $this->organization->id,
            'user_id' => $this->admin->id,
        ]);

        // Restore with merge mode
        $restoreResult = $this->restoreService->restoreFromBackup(
            $backupResult['filename'],
            'merge'
        );

        $this->assertTrue($restoreResult['success']);

        // Both clients should exist
        $this->assertEquals(2, Client::count());
        $this->assertDatabaseHas('clients', ['id' => $client1->id]);
        $this->assertDatabaseHas('clients', ['id' => $client2->id]);

        // Cleanup
        Storage::disk('local')->delete('backups/' . $backupResult['filename']);
    }

    /**
     * Test backup with forbidden tables is rejected
     *
     * @test
     */
    public function backup_with_forbidden_tables_is_rejected()
    {
        // Create malicious backup manually
        $maliciousBackup = [
            'meta' => ['created_at' => now()->toIso8601String()],
            'data' => [
                'sessions' => [
                    ['id' => 'admin_session', 'user_id' => 1, 'payload' => 'fake'],
                ],
                'password_resets' => [
                    ['email' => 'admin@example.com', 'token' => 'known_token'],
                ],
            ],
        ];

        $filename = 'malicious_backup_' . time() . '.json';
        Storage::disk('local')->put('backups/' . $filename, json_encode($maliciousBackup));

        // Attempt restore
        $restoreResult = $this->restoreService->restoreFromBackup($filename, 'merge');

        // Verify restore was blocked
        $this->assertFalse($restoreResult['success']);
        $this->assertStringContainsString('unauthorized tables', $restoreResult['error']);
        $this->assertStringContainsString('sessions', $restoreResult['error']);
        $this->assertStringContainsString('password_resets', $restoreResult['error']);

        // Verify no data was imported
        $this->assertEmpty($restoreResult['imported']);

        // Cleanup
        Storage::disk('local')->delete('backups/' . $filename);
    }

    /**
     * Test preview shows what would be restored
     *
     * @test
     */
    public function preview_shows_restore_details_without_restoring()
    {
        $client = Client::factory()->create([
            'organization_id' => $this->organization->id,
            'user_id' => $this->admin->id,
        ]);

        // Create backup
        $backupResult = $this->backupService->createBackup(['clients']);

        // Delete client
        $originalCount = Client::count();
        $client->delete();
        $this->assertEquals($originalCount - 1, Client::count());

        // Preview restore
        $preview = $this->restoreService->previewRestore($backupResult['filename']);

        $this->assertTrue($preview['success']);
        $this->assertArrayHasKey('tables', $preview);
        $this->assertArrayHasKey('clients', $preview['tables']);

        // Verify preview data
        $clientPreview = $preview['tables']['clients'];
        $this->assertTrue($clientPreview['allowed']);
        $this->assertTrue($clientPreview['exists']);
        $this->assertEquals(1, $clientPreview['rows_in_backup']);
        $this->assertEquals($originalCount - 1, $clientPreview['rows_in_database']);
        $this->assertEquals('OK', $clientPreview['status']);

        // Verify no data was actually restored
        $this->assertEquals($originalCount - 1, Client::count());

        // Cleanup
        Storage::disk('local')->delete('backups/' . $backupResult['filename']);
    }

    /**
     * Test end-to-end backup via HTTP endpoint
     *
     * @test
     */
    public function end_to_end_backup_via_http_endpoint()
    {
        // Create test data
        $client = Client::factory()->create([
            'organization_id' => $this->organization->id,
            'user_id' => $this->admin->id,
        ]);

        // Create backup via HTTP
        $response = $this->actingAs($this->admin)
            ->postJson('/settings/backup/export', [
                'tables' => ['clients'],
            ]);

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'success',
            'filename',
            'download_url',
            'size',
            'tables_count',
        ]);

        $filename = $response->json('filename');

        // Download backup via HTTP
        $downloadResponse = $this->actingAs($this->admin)
            ->get('/settings/backup/download/' . $filename);

        $downloadResponse->assertSuccessful();
        $downloadResponse->assertHeader('Content-Type', 'application/json');

        // Verify downloaded content
        $backupData = json_decode($downloadResponse->getContent(), true);
        $this->assertArrayHasKey('data', $backupData);
        $this->assertArrayHasKey('clients', $backupData['data']);

        // Cleanup
        Storage::disk('local')->delete('backups/' . $filename);
    }
}
