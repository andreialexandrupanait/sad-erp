<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->isOrgAdmin() || auth()->user()->isSuperAdmin();
    }

    public function rules(): array
    {
        $userId = $this->route('user')->id;

        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'role' => ['required', Rule::in(['admin', 'manager', 'user'])],
            'phone' => 'nullable|string|max:50',
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ];
    }
}
