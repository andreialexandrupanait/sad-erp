<?php

namespace Tests\Unit\Policies;

use App\Models\FinancialRevenue;
use App\Models\Organization;
use App\Models\User;
use App\Policies\FinancialRevenuePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test suite for FinancialRevenuePolicy
 *
 * @covers \App\Policies\FinancialRevenuePolicy
 */
class FinancialRevenuePolicyTest extends TestCase
{
    use RefreshDatabase;

    protected FinancialRevenuePolicy $policy;
    protected Organization $organization;
    protected Organization $otherOrganization;
    protected User $user;
    protected User $admin;
    protected User $otherUser;
    protected FinancialRevenue $revenue;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new FinancialRevenuePolicy();

        // Create organizations
        $this->organization = Organization::factory()->create();
        $this->otherOrganization = Organization::factory()->create();

        // Create users
        $this->user = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role' => 'user',
        ]);

        $this->admin = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role' => 'admin',
        ]);

        $this->otherUser = User::factory()->create([
            'organization_id' => $this->otherOrganization->id,
            'role' => 'user',
        ]);

        // Create a revenue owned by regular user
        $this->revenue = FinancialRevenue::factory()->create([
            'organization_id' => $this->organization->id,
            'user_id' => $this->user->id,
        ]);
    }

    /** @test */
    public function any_user_can_view_any_revenues(): void
    {
        $this->assertTrue($this->policy->viewAny($this->user));
        $this->assertTrue($this->policy->viewAny($this->admin));
        $this->assertTrue($this->policy->viewAny($this->otherUser));
    }

    /** @test */
    public function user_can_view_revenue_from_same_organization(): void
    {
        $this->assertTrue($this->policy->view($this->user, $this->revenue));
    }

    /** @test */
    public function user_cannot_view_revenue_from_different_organization(): void
    {
        $this->assertFalse($this->policy->view($this->otherUser, $this->revenue));
    }

    /** @test */
    public function admin_can_view_revenue_from_same_organization(): void
    {
        $this->assertTrue($this->policy->view($this->admin, $this->revenue));
    }

    /** @test */
    public function any_user_can_create_revenues(): void
    {
        $this->assertTrue($this->policy->create($this->user));
        $this->assertTrue($this->policy->create($this->admin));
    }

    /** @test */
    public function owner_can_update_own_revenue(): void
    {
        $this->assertTrue($this->policy->update($this->user, $this->revenue));
    }

    /** @test */
    public function user_can_update_any_revenue_in_same_org(): void
    {
        // Policy is organization-scoped: any user in the org can update
        $anotherUser = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role' => 'user',
        ]);

        $this->assertTrue($this->policy->update($anotherUser, $this->revenue));
    }

    /** @test */
    public function admin_can_update_any_revenue_in_same_org(): void
    {
        $this->assertTrue($this->policy->update($this->admin, $this->revenue));
    }

    /** @test */
    public function user_cannot_update_revenue_from_different_org(): void
    {
        $this->assertFalse($this->policy->update($this->otherUser, $this->revenue));
    }

    /** @test */
    public function owner_can_delete_own_revenue(): void
    {
        $this->assertTrue($this->policy->delete($this->user, $this->revenue));
    }

    /** @test */
    public function user_can_delete_any_revenue_in_same_org(): void
    {
        // Policy is organization-scoped: any user in the org can delete
        $anotherUser = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role' => 'user',
        ]);

        $this->assertTrue($this->policy->delete($anotherUser, $this->revenue));
    }

    /** @test */
    public function admin_can_delete_any_revenue_in_same_org(): void
    {
        $this->assertTrue($this->policy->delete($this->admin, $this->revenue));
    }

    /** @test */
    public function user_cannot_delete_revenue_from_different_org(): void
    {
        $this->assertFalse($this->policy->delete($this->otherUser, $this->revenue));
    }

    /** @test */
    public function owner_can_restore_own_revenue(): void
    {
        $this->assertTrue($this->policy->restore($this->user, $this->revenue));
    }

    /** @test */
    public function admin_can_restore_any_revenue_in_same_org(): void
    {
        $this->assertTrue($this->policy->restore($this->admin, $this->revenue));
    }

    /** @test */
    public function user_cannot_restore_revenue_from_different_org(): void
    {
        $this->assertFalse($this->policy->restore($this->otherUser, $this->revenue));
    }

    /** @test */
    public function only_admin_can_force_delete_revenue(): void
    {
        $this->assertTrue($this->policy->forceDelete($this->admin, $this->revenue));
        $this->assertFalse($this->policy->forceDelete($this->user, $this->revenue));
    }

    /** @test */
    public function admin_cannot_force_delete_revenue_from_different_org(): void
    {
        $otherOrgAdmin = User::factory()->create([
            'organization_id' => $this->otherOrganization->id,
            'role' => 'admin',
        ]);

        $this->assertFalse($this->policy->forceDelete($otherOrgAdmin, $this->revenue));
    }

    /** @test */
    public function any_user_can_import(): void
    {
        $this->assertTrue($this->policy->import($this->user));
        $this->assertTrue($this->policy->import($this->admin));
    }

    /** @test */
    public function any_user_can_export(): void
    {
        $this->assertTrue($this->policy->export($this->user));
        $this->assertTrue($this->policy->export($this->admin));
    }

    /** @test */
    public function admin_has_elevated_privileges(): void
    {
        // Note: 'superadmin' is not a valid role in the enum, using 'admin' instead
        $admin = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role' => 'admin',
        ]);

        $this->assertTrue($this->policy->update($admin, $this->revenue));
        $this->assertTrue($this->policy->delete($admin, $this->revenue));
        $this->assertTrue($this->policy->forceDelete($admin, $this->revenue));
    }
}
