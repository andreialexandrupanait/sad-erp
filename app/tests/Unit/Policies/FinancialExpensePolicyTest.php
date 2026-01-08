<?php

namespace Tests\Unit\Policies;

use App\Models\FinancialExpense;
use App\Models\Organization;
use App\Models\User;
use App\Policies\FinancialExpensePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test suite for FinancialExpensePolicy
 *
 * @covers \App\Policies\FinancialExpensePolicy
 */
class FinancialExpensePolicyTest extends TestCase
{
    use RefreshDatabase;

    protected FinancialExpensePolicy $policy;
    protected Organization $organization;
    protected Organization $otherOrganization;
    protected User $user;
    protected User $admin;
    protected User $otherUser;
    protected FinancialExpense $expense;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new FinancialExpensePolicy();

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

        // Create an expense owned by regular user
        $this->expense = FinancialExpense::factory()->create([
            'organization_id' => $this->organization->id,
            'user_id' => $this->user->id,
        ]);
    }

    /** @test */
    public function user_with_organization_can_view_any_expenses(): void
    {
        $this->assertTrue($this->policy->viewAny($this->user));
        $this->assertTrue($this->policy->viewAny($this->admin));
        $this->assertTrue($this->policy->viewAny($this->otherUser));
    }

    /** @test */
    public function user_without_organization_cannot_view_any_expenses(): void
    {
        $userWithoutOrg = User::factory()->create([
            'organization_id' => null,
            'role' => 'user',
        ]);

        $this->assertFalse($this->policy->viewAny($userWithoutOrg));
    }

    /** @test */
    public function user_can_view_expense_from_same_organization(): void
    {
        $this->assertTrue($this->policy->view($this->user, $this->expense));
    }

    /** @test */
    public function user_cannot_view_expense_from_different_organization(): void
    {
        $this->assertFalse($this->policy->view($this->otherUser, $this->expense));
    }

    /** @test */
    public function admin_can_view_expense_from_same_organization(): void
    {
        $this->assertTrue($this->policy->view($this->admin, $this->expense));
    }

    /** @test */
    public function user_with_organization_can_create_expenses(): void
    {
        $this->assertTrue($this->policy->create($this->user));
        $this->assertTrue($this->policy->create($this->admin));
    }

    /** @test */
    public function user_without_organization_cannot_create_expenses(): void
    {
        $userWithoutOrg = User::factory()->create([
            'organization_id' => null,
            'role' => 'user',
        ]);

        $this->assertFalse($this->policy->create($userWithoutOrg));
    }

    /** @test */
    public function owner_can_update_own_expense(): void
    {
        $this->assertTrue($this->policy->update($this->user, $this->expense));
    }

    /** @test */
    public function user_can_update_any_expense_in_same_org(): void
    {
        // Policy is organization-scoped: any user in the org can update
        $anotherUser = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role' => 'user',
        ]);

        $this->assertTrue($this->policy->update($anotherUser, $this->expense));
    }

    /** @test */
    public function admin_can_update_any_expense_in_same_org(): void
    {
        $this->assertTrue($this->policy->update($this->admin, $this->expense));
    }

    /** @test */
    public function user_cannot_update_expense_from_different_org(): void
    {
        $this->assertFalse($this->policy->update($this->otherUser, $this->expense));
    }

    /** @test */
    public function owner_can_delete_own_expense(): void
    {
        $this->assertTrue($this->policy->delete($this->user, $this->expense));
    }

    /** @test */
    public function user_can_delete_any_expense_in_same_org(): void
    {
        // Policy is organization-scoped: any user in the org can delete
        $anotherUser = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role' => 'user',
        ]);

        $this->assertTrue($this->policy->delete($anotherUser, $this->expense));
    }

    /** @test */
    public function admin_can_delete_any_expense_in_same_org(): void
    {
        $this->assertTrue($this->policy->delete($this->admin, $this->expense));
    }

    /** @test */
    public function user_cannot_delete_expense_from_different_org(): void
    {
        $this->assertFalse($this->policy->delete($this->otherUser, $this->expense));
    }

    /** @test */
    public function owner_can_restore_own_expense(): void
    {
        $this->assertTrue($this->policy->restore($this->user, $this->expense));
    }

    /** @test */
    public function admin_can_restore_any_expense_in_same_org(): void
    {
        $this->assertTrue($this->policy->restore($this->admin, $this->expense));
    }

    /** @test */
    public function user_cannot_restore_expense_from_different_org(): void
    {
        $this->assertFalse($this->policy->restore($this->otherUser, $this->expense));
    }

    /** @test */
    public function only_admin_can_force_delete_expense(): void
    {
        $this->assertTrue($this->policy->forceDelete($this->admin, $this->expense));
        $this->assertFalse($this->policy->forceDelete($this->user, $this->expense));
    }

    /** @test */
    public function admin_cannot_force_delete_expense_from_different_org(): void
    {
        $otherOrgAdmin = User::factory()->create([
            'organization_id' => $this->otherOrganization->id,
            'role' => 'admin',
        ]);

        $this->assertFalse($this->policy->forceDelete($otherOrgAdmin, $this->expense));
    }

    /** @test */
    public function user_with_organization_can_import(): void
    {
        $this->assertTrue($this->policy->import($this->user));
        $this->assertTrue($this->policy->import($this->admin));
    }

    /** @test */
    public function user_without_organization_cannot_import(): void
    {
        $userWithoutOrg = User::factory()->create([
            'organization_id' => null,
            'role' => 'user',
        ]);

        $this->assertFalse($this->policy->import($userWithoutOrg));
    }

    /** @test */
    public function user_with_organization_can_export(): void
    {
        $this->assertTrue($this->policy->export($this->user));
        $this->assertTrue($this->policy->export($this->admin));
    }

    /** @test */
    public function user_without_organization_cannot_export(): void
    {
        $userWithoutOrg = User::factory()->create([
            'organization_id' => null,
            'role' => 'user',
        ]);

        $this->assertFalse($this->policy->export($userWithoutOrg));
    }

    /** @test */
    public function admin_has_elevated_privileges(): void
    {
        // Note: 'superadmin' is not a valid role in the enum, using 'admin' instead
        $admin = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role' => 'admin',
        ]);

        $this->assertTrue($this->policy->update($admin, $this->expense));
        $this->assertTrue($this->policy->delete($admin, $this->expense));
        $this->assertTrue($this->policy->forceDelete($admin, $this->expense));
    }
}
