<?php

namespace Tests\Unit\Policies;

use App\Models\BankingCredential;
use App\Models\Organization;
use App\Models\User;
use App\Policies\BankingCredentialPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test suite for BankingCredentialPolicy
 *
 * @covers \App\Policies\BankingCredentialPolicy
 */
class BankingCredentialPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected BankingCredentialPolicy $policy;
    protected Organization $organization;
    protected Organization $otherOrganization;
    protected User $user;
    protected User $otherUser;
    protected User $sameOrgOtherUser;
    protected BankingCredential $credential;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new BankingCredentialPolicy();

        // Create organizations
        $this->organization = Organization::factory()->create();
        $this->otherOrganization = Organization::factory()->create();

        // Create users
        $this->user = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role' => 'user',
        ]);

        $this->sameOrgOtherUser = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role' => 'user',
        ]);

        $this->otherUser = User::factory()->create([
            'organization_id' => $this->otherOrganization->id,
            'role' => 'user',
        ]);

        // Create a banking credential owned by regular user
        $this->credential = BankingCredential::factory()->create([
            'organization_id' => $this->organization->id,
            'user_id' => $this->user->id,
        ]);
    }

    /** @test */
    public function user_with_organization_can_view_any_credentials(): void
    {
        $this->assertTrue($this->policy->viewAny($this->user));
        $this->assertTrue($this->policy->viewAny($this->otherUser));
    }

    /** @test */
    public function user_without_organization_cannot_view_any_credentials(): void
    {
        $userWithoutOrg = User::factory()->create([
            'organization_id' => null,
            'role' => 'user',
        ]);

        $this->assertFalse($this->policy->viewAny($userWithoutOrg));
    }

    /** @test */
    public function owner_can_view_own_credential(): void
    {
        $this->assertTrue($this->policy->view($this->user, $this->credential));
    }

    /** @test */
    public function user_cannot_view_other_users_credential_even_in_same_org(): void
    {
        $this->assertFalse($this->policy->view($this->sameOrgOtherUser, $this->credential));
    }

    /** @test */
    public function user_cannot_view_credential_from_different_organization(): void
    {
        $this->assertFalse($this->policy->view($this->otherUser, $this->credential));
    }

    /** @test */
    public function user_with_organization_can_create_credentials(): void
    {
        $this->assertTrue($this->policy->create($this->user));
    }

    /** @test */
    public function user_without_organization_cannot_create_credentials(): void
    {
        $userWithoutOrg = User::factory()->create([
            'organization_id' => null,
            'role' => 'user',
        ]);

        $this->assertFalse($this->policy->create($userWithoutOrg));
    }

    /** @test */
    public function owner_can_update_own_credential(): void
    {
        $this->assertTrue($this->policy->update($this->user, $this->credential));
    }

    /** @test */
    public function user_cannot_update_other_users_credential_even_in_same_org(): void
    {
        $this->assertFalse($this->policy->update($this->sameOrgOtherUser, $this->credential));
    }

    /** @test */
    public function user_cannot_update_credential_from_different_org(): void
    {
        $this->assertFalse($this->policy->update($this->otherUser, $this->credential));
    }

    /** @test */
    public function owner_can_delete_own_credential(): void
    {
        $this->assertTrue($this->policy->delete($this->user, $this->credential));
    }

    /** @test */
    public function user_cannot_delete_other_users_credential_even_in_same_org(): void
    {
        $this->assertFalse($this->policy->delete($this->sameOrgOtherUser, $this->credential));
    }

    /** @test */
    public function user_cannot_delete_credential_from_different_org(): void
    {
        $this->assertFalse($this->policy->delete($this->otherUser, $this->credential));
    }

    /** @test */
    public function admin_cannot_access_other_users_credentials(): void
    {
        // Banking credentials are personal - even admins cannot access other users' credentials
        $admin = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role' => 'admin',
        ]);

        $this->assertFalse($this->policy->view($admin, $this->credential));
        $this->assertFalse($this->policy->update($admin, $this->credential));
        $this->assertFalse($this->policy->delete($admin, $this->credential));
    }
}
