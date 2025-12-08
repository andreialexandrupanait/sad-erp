<?php

namespace Tests\Feature\Middleware;

use Tests\TestCase;
use App\Models\User;
use App\Models\Organization;
use App\Models\Credential;
use App\Models\InternalAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class PasswordConfirmationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Organization $organization;
    protected Credential $credential;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organization = Organization::factory()->create();

        $this->user = User::factory()->create([
            'organization_id' => $this->organization->id,
            'password' => Hash::make('correct-password'),
        ]);

        $this->credential = Credential::factory()->create([
            'organization_id' => $this->organization->id,
            'password' => 'encrypted-credential-password',
        ]);
    }

    /**
     * Test password reveal requires confirmation
     *
     * @test
     */
    public function it_requires_password_confirmation_to_reveal_credentials()
    {
        $response = $this->actingAs($this->user)
            ->postJson("/credentials/{$this->credential->id}/reveal-password");

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'requires_confirmation' => true,
        ]);
        $response->assertJsonFragment(['message' => 'Password confirmation is required for this action.']);
    }

    /**
     * Test wrong password is rejected
     *
     * @test
     */
    public function it_rejects_wrong_password()
    {
        $response = $this->actingAs($this->user)
            ->postJson("/credentials/{$this->credential->id}/reveal-password", [
                'current_password' => 'wrong-password',
            ]);

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'requires_confirmation' => true,
        ]);
        $response->assertJsonFragment([
            'message' => 'The provided password does not match your current password.'
        ]);

        // Verify failed attempt was logged
        $this->assertDatabaseHas('logs', [
            'level' => 'warning',
            'message' => 'Failed password confirmation attempt',
            'context->user_id' => $this->user->id,
        ]);
    }

    /**
     * Test correct password allows password reveal
     *
     * @test
     */
    public function it_allows_password_reveal_with_correct_password()
    {
        $response = $this->actingAs($this->user)
            ->postJson("/credentials/{$this->credential->id}/reveal-password", [
                'current_password' => 'correct-password',
            ]);

        $response->assertSuccessful();
        $response->assertJsonStructure(['password']);

        // Verify success was logged
        $this->assertDatabaseHas('logs', [
            'level' => 'info',
            'message' => 'Password confirmation successful',
            'context->user_id' => $this->user->id,
        ]);
    }

    /**
     * Test internal account password reveal also requires confirmation
     *
     * @test
     */
    public function it_requires_confirmation_for_internal_account_passwords()
    {
        $internalAccount = InternalAccount::factory()->create([
            'organization_id' => $this->organization->id,
            'password' => 'encrypted-account-password',
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/internal-accounts/{$internalAccount->id}/reveal-password");

        $response->assertStatus(403);
        $response->assertJson(['requires_confirmation' => true]);
    }

    /**
     * Test middleware logs failed attempts with IP and user agent
     *
     * @test
     */
    public function it_logs_failed_attempts_with_context()
    {
        $response = $this->actingAs($this->user)
            ->withHeaders([
                'User-Agent' => 'AttackerBot/1.0',
                'X-Forwarded-For' => '192.168.1.100',
            ])
            ->postJson("/credentials/{$this->credential->id}/reveal-password", [
                'current_password' => 'wrong-password',
            ]);

        $response->assertStatus(403);

        // Verify comprehensive logging
        $this->assertDatabaseHas('logs', [
            'message' => 'Failed password confirmation attempt',
            'context->user_email' => $this->user->email,
            'context->ip_address' => '192.168.1.100',
            'context->user_agent' => 'AttackerBot/1.0',
        ]);
    }

    /**
     * Test rate limiting prevents brute force attacks
     *
     * @test
     */
    public function it_prevents_brute_force_password_attempts()
    {
        // Make multiple failed attempts
        for ($i = 0; $i < 6; $i++) {
            $response = $this->actingAs($this->user)
                ->postJson("/credentials/{$this->credential->id}/reveal-password", [
                    'current_password' => 'wrong-password-' . $i,
                ]);
        }

        // After 5 failed attempts, should be rate limited
        $response->assertStatus(429); // Too Many Requests
    }

    /**
     * Test middleware doesn't interfere with other routes
     *
     * @test
     */
    public function it_only_applies_to_password_reveal_routes()
    {
        // Regular credential view shouldn't require password confirmation
        $response = $this->actingAs($this->user)
            ->get("/credentials/{$this->credential->id}");

        $response->assertSuccessful();
        // No password confirmation required for regular views
    }

    /**
     * Test session hijacking scenario
     *
     * @test
     */
    public function it_protects_against_session_hijacking()
    {
        // Simulate: Attacker steals session cookie but doesn't know password

        // Attacker tries to reveal password without knowing user's password
        $response = $this->actingAs($this->user)
            ->postJson("/credentials/{$this->credential->id}/reveal-password", [
                'current_password' => 'guessed-password',
            ]);

        $response->assertStatus(403);

        // Verify the real password was never exposed
        $response->assertJsonMissing(['password' => $this->credential->password]);
    }

    /**
     * Test password confirmation works across different sessions
     *
     * @test
     */
    public function it_requires_confirmation_in_each_session()
    {
        // First session - provide password
        $response1 = $this->actingAs($this->user)
            ->postJson("/credentials/{$this->credential->id}/reveal-password", [
                'current_password' => 'correct-password',
            ]);

        $response1->assertSuccessful();

        // New session - must provide password again (no persistent confirmation)
        $response2 = $this->actingAs($this->user)
            ->postJson("/credentials/{$this->credential->id}/reveal-password");

        $response2->assertStatus(403);
        $response2->assertJson(['requires_confirmation' => true]);
    }

    /**
     * Test empty password is rejected
     *
     * @test
     */
    public function it_rejects_empty_password()
    {
        $response = $this->actingAs($this->user)
            ->postJson("/credentials/{$this->credential->id}/reveal-password", [
                'current_password' => '',
            ]);

        $response->assertStatus(403);
    }

    /**
     * Test unauthenticated users cannot bypass confirmation
     *
     * @test
     */
    public function it_requires_authentication_before_confirmation()
    {
        $response = $this->postJson("/credentials/{$this->credential->id}/reveal-password", [
            'current_password' => 'any-password',
        ]);

        $response->assertStatus(401); // Unauthenticated
    }
}
