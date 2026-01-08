<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->isOrgAdmin() || auth()->user()->isSuperAdmin();
    }

    public function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
            ],
            'role' => ['required', Rule::in(['admin', 'manager', 'user'])],
            'phone' => 'nullable|string|max:50',
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
            'password_option' => ['required', Rule::in(['manual', 'invite'])],
        ];

        // Password required only for manual option
        if ($this->password_option === 'manual') {
            $rules['password'] = ['required', 'confirmed', Password::defaults()];
        }

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'status' => $this->status ?? 'active',
        ]);
    }

    public function messages(): array
    {
        return [
            'password_option.required' => __('Please select how to set the password.'),
            'password.required' => __('Password is required when setting manually.'),
        ];
    }
}
