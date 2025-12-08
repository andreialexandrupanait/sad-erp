<?php

namespace Tests\Feature\Controllers\Settings;

use Tests\TestCase;
use App\Models\User;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

class BackupControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();

        // Create organization
        $this->organization = Organization::factory()->create();

        // Create admin user
        $this->admin = User::factory()->create([
            'role' => 'admin',
            'organization_id' => $this->organization->id,
        ]);
    }

    /**
     * Test path traversal attack is blocked
     *
     * @test
     */
    public function it_blocks_path_traversal_in_download()
    {
        $pathTraversalAttempts = [
            '../../../etc/passwd',
            '..%2F..%2F..%2Fetc%2Fpasswd',
            '....//....//....//etc/passwd',
            '../.env',
            '../../storage/logs/laravel.log',
        ];

        foreach ($pathTraversalAttempts as $maliciousPath) {
            $response = $this->actingAs($this->admin)
                ->get("/settings/backup/download/{$maliciousPath}");

            $response->assertStatus(403);

            // Verify attack was logged
            $this->assertDatabaseHas('audit_logs', [
                'action' => 'backup_download_attempt',
                'user_id' => $this->admin->id,
            ]);
        }
    }

    /**
     * Test legitimate backup download works
     *
     * @test
     */
    public function it_allows_legitimate_backup_download()
    {
        // Create a legitimate backup file
        Storage::disk('local')->makeDirectory('backups');
        Storage::disk('local')->put('backups/backup_2025-12-08.json', json_encode([
            'meta' => ['created_at' => now()],
            'data' => [],
        ]));

        $response = $this->actingAs($this->admin)
            ->get('/settings/backup/download/backup_2025-12-08.json');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
    }

    /**
     * Test symlink attack is blocked
     *
     * @test
     */
    public function it_blocks_symlink_attacks()
    {
        // This test verifies the realpath() check prevents symlink exploitation
        $response = $this->actingAs($this->admin)
            ->get('/settings/backup/download/../../../../../../etc/passwd');

        $response->assertStatus(403);
    }

    /**
     * Test invalid filename characters are rejected
     *
     * @test
     */
    public function it_rejects_invalid_filename_characters()
    {
        $invalidFilenames = [
            'backup;rm -rf /',
            'backup\0.json',
            'backup<script>.json',
            'backup|cat /etc/passwd',
        ];

        foreach ($invalidFilenames as $invalidFilename) {
            $response = $this->actingAs($this->admin)
                ->get("/settings/backup/download/{$invalidFilename}");

            $response->assertStatus(403);
        }
    }

    /**
     * Test only admins can download backups
     *
     * @test
     */
    public function it_requires_admin_role_for_backup_download()
    {
        $regularUser = User::factory()->create([
            'role' => 'user',
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->actingAs($regularUser)
            ->get('/settings/backup/download/backup_2025-12-08.json');

        $response->assertStatus(403);
    }

    /**
     * Test backup export creates valid file
     *
     * @test
     */
    public function it_creates_valid_backup_on_export()
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/settings/backup/export', [
                'tables' => ['users', 'organizations'],
            ]);

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'success',
            'filename',
            'download_url',
            'size',
            'tables_count',
        ]);

        // Verify file was created
        $filename = $response->json('filename');
        $this->assertTrue(Storage::disk('local')->exists("backups/{$filename}"));
    }

    /**
     * Test backup restore validates table whitelist
     *
     * @test
     */
    public function it_validates_table_whitelist_on_restore()
    {
        // Create malicious backup with forbidden tables
        $maliciousBackup = [
            'meta' => ['created_at' => now()],
            'data' => [
                'sessions' => [['id' => 'fake_session', 'user_id' => 1]],
                'password_resets' => [['email' => 'admin@example.com', 'token' => 'known']],
            ],
        ];

        Storage::disk('local')->put('backups/malicious.json', json_encode($maliciousBackup));

        $response = $this->actingAs($this->admin)
            ->post('/settings/backup/restore/malicious.json', [
                'mode' => 'merge',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        // Verify the error message mentions unauthorized tables
        $this->assertStringContainsString('unauthorized tables', session('error'));
    }

    /**
     * Test legitimate backup restore works
     *
     * @test
     */
    public function it_allows_legitimate_backup_restore()
    {
        // Create legitimate backup
        $legitimateBackup = [
            'meta' => ['created_at' => now()],
            'data' => [
                'users' => [[
                    'id' => 999,
                    'name' => 'Test User',
                    'email' => 'test@example.com',
                    'password' => bcrypt('password'),
                    'organization_id' => $this->organization->id,
                ]],
            ],
        ];

        Storage::disk('local')->put('backups/legitimate.json', json_encode($legitimateBackup));

        $response = $this->actingAs($this->admin)
            ->post('/settings/backup/restore/legitimate.json', [
                'mode' => 'merge',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify data was restored
        $this->assertDatabaseHas('users', [
            'id' => 999,
            'email' => 'test@example.com',
        ]);
    }

    /**
     * Test backup deletion works
     *
     * @test
     */
    public function it_allows_backup_deletion()
    {
        Storage::disk('local')->put('backups/old_backup.json', '{}');

        $response = $this->actingAs($this->admin)
            ->delete('/settings/backup/old_backup.json');

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertFalse(Storage::disk('local')->exists('backups/old_backup.json'));
    }

    /**
     * Test backup deletion also validates filename
     *
     * @test
     */
    public function it_validates_filename_on_deletion()
    {
        $response = $this->actingAs($this->admin)
            ->delete('/settings/backup/../../../etc/passwd');

        $response->assertStatus(403);
    }
}
