<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function createUserWithOrganization(array $attributes = []): User
    {
        $organization = Organization::factory()->create();

        return User::factory()->create(array_merge([
            'organization_id' => $organization->id,
        ], $attributes));
    }

    public function test_login_page_is_accessible(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_login_with_valid_credentials(): void
    {
        $user = $this->createUserWithOrganization([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'two_factor_secret' => null,
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
    }

    public function test_users_cannot_login_with_invalid_password(): void
    {
        $user = $this->createUserWithOrganization([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_cannot_login_with_non_existent_email(): void
    {
        $response = $this->post('/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password',
        ]);

        $this->assertGuest();
    }

    public function test_authenticated_users_can_logout(): void
    {
        $user = $this->createUserWithOrganization();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
    }

    public function test_unauthenticated_users_are_redirected_from_dashboard(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_users_can_access_dashboard(): void
    {
        $user = $this->createUserWithOrganization([
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
    }

    public function test_root_redirects_to_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }

    public function test_profile_page_requires_authentication(): void
    {
        $response = $this->get('/profile');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_users_can_access_profile(): void
    {
        $user = $this->createUserWithOrganization();

        $response = $this->actingAs($user)->get('/profile');

        $response->assertStatus(200);
    }

    public function test_login_is_rate_limited(): void
    {
        $user = $this->createUserWithOrganization([
            'email' => 'test@example.com',
        ]);

        // Attempt to login 6 times with wrong password (limit is typically 5)
        for ($i = 0; $i < 6; $i++) {
            $this->post('/login', [
                'email' => 'test@example.com',
                'password' => 'wrong-password',
            ]);
        }

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(429);
    }
}
