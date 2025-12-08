<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserPermissionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->isOrgAdmin() || auth()->user()->isSuperAdmin();
    }

    public function rules(): array
    {
        return [
            'permissions' => 'required|array',
            'permissions.*' => 'array',
            'permissions.*.can_view' => 'boolean',
            'permissions.*.can_create' => 'boolean',
            'permissions.*.can_update' => 'boolean',
            'permissions.*.can_delete' => 'boolean',
            'permissions.*.can_export' => 'boolean',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Convert checkbox values to booleans
        $permissions = $this->permissions ?? [];
        foreach ($permissions as $module => $perms) {
            $permissions[$module] = [
                'can_view' => isset($perms['can_view']) && $perms['can_view'],
                'can_create' => isset($perms['can_create']) && $perms['can_create'],
                'can_update' => isset($perms['can_update']) && $perms['can_update'],
                'can_delete' => isset($perms['can_delete']) && $perms['can_delete'],
                'can_export' => isset($perms['can_export']) && $perms['can_export'],
            ];
        }
        $this->merge(['permissions' => $permissions]);
    }
}
