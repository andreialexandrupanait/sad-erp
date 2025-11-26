<?php

namespace App\Http\Requests\InternalAccount;

use Illuminate\Foundation\Http\FormRequest;

class StoreInternalAccountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'account_name' => ['required', 'string', 'max:255'],
            'url' => ['nullable', 'url', 'max:255'],
            'username' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:500'],
            'team_accessible' => ['boolean'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'team_accessible' => $this->has('team_accessible'),
        ]);
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'account_name' => __('account name'),
            'url' => __('URL'),
            'username' => __('username'),
            'password' => __('password'),
            'team_accessible' => __('team accessible'),
            'notes' => __('notes'),
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'account_name.required' => __('Please enter an account name.'),
            'account_name.max' => __('The account name cannot exceed :max characters.'),
            'url.url' => __('Please enter a valid URL.'),
            'url.max' => __('The URL cannot exceed :max characters.'),
            'username.max' => __('The username cannot exceed :max characters.'),
            'password.max' => __('The password cannot exceed :max characters.'),
            'notes.max' => __('The notes cannot exceed :max characters.'),
        ];
    }
}
