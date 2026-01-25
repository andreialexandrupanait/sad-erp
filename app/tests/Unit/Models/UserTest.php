<?php

namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_has_fillable_attributes(): void
    {
        $user = new User();

        $this->assertContains('name', $user->getFillable());
        $this->assertContains('email', $user->getFillable());
        $this->assertContains('password', $user->getFillable());
        $this->assertContains('role', $user->getFillable());
    }

    public function test_password_is_hidden_from_serialization(): void
    {
        $user = new User();

        $this->assertContains('password', $user->getHidden());
        $this->assertContains('remember_token', $user->getHidden());
        $this->assertContains('two_factor_secret', $user->getHidden());
    }

    public function test_user_initials_with_single_name(): void
    {
        $user = new User(['name' => 'John']);

        $this->assertEquals('J', $user->initials);
    }

    public function test_user_initials_with_full_name(): void
    {
        $user = new User(['name' => 'John Doe']);

        $this->assertEquals('JD', $user->initials);
    }

    public function test_user_initials_with_three_part_name(): void
    {
        $user = new User(['name' => 'John Michael Doe']);

        $this->assertEquals('JM', $user->initials);
    }

    public function test_superadmin_role_detection(): void
    {
        $user = new User(['role' => 'superadmin']);

        $this->assertTrue($user->isSuperAdmin());
        $this->assertFalse($user->isAdmin());
    }

    public function test_admin_role_detection(): void
    {
        $user = new User(['role' => 'admin']);

        $this->assertFalse($user->isSuperAdmin());
        $this->assertTrue($user->isAdmin());
        $this->assertTrue($user->isOrgAdmin());
    }

    public function test_manager_role_detection(): void
    {
        $user = new User(['role' => 'manager']);

        $this->assertTrue($user->isManager());
        $this->assertTrue($user->canManage());
    }

    public function test_user_role_cannot_manage(): void
    {
        $user = new User(['role' => 'user']);

        $this->assertFalse($user->isManager());
        $this->assertFalse($user->canManage());
    }

    public function test_role_label_translation(): void
    {
        $superadmin = new User(['role' => 'superadmin']);
        $admin = new User(['role' => 'admin']);
        $manager = new User(['role' => 'manager']);
        $user = new User(['role' => 'user']);
        $viewer = new User(['role' => 'viewer']);

        $this->assertEquals('Super Admin', $superadmin->role_label);
        $this->assertEquals('Administrator', $admin->role_label);
        $this->assertEquals('Manager', $manager->role_label);
        $this->assertEquals('User', $user->role_label);
        $this->assertEquals('Viewer', $viewer->role_label);
    }

    public function test_two_factor_not_enabled_by_default(): void
    {
        $user = new User();

        $this->assertFalse($user->hasTwoFactorEnabled());
    }

    public function test_user_settings_default_to_empty(): void
    {
        $user = new User();

        $this->assertNull($user->getSetting('non_existent_key'));
        $this->assertEquals('default', $user->getSetting('non_existent_key', 'default'));
    }

    public function test_avatar_url_returns_null_when_no_avatar(): void
    {
        $user = new User();

        $this->assertNull($user->avatar_url);
    }

    public function test_superadmin_can_access_any_module(): void
    {
        $user = new User(['role' => 'superadmin']);

        $this->assertTrue($user->canAccessModule('clients', 'view'));
        $this->assertTrue($user->canAccessModule('clients', 'create'));
        $this->assertTrue($user->canAccessModule('clients', 'delete'));
        $this->assertTrue($user->canAccessModule('any_module', 'any_action'));
    }

    public function test_org_admin_can_access_any_module(): void
    {
        $user = new User(['role' => 'admin']);

        $this->assertTrue($user->canAccessModule('clients', 'view'));
        $this->assertTrue($user->canAccessModule('finance', 'delete'));
    }
}
