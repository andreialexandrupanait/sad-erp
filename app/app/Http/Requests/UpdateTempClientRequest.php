<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTempClientRequest extends FormRequest
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
            'temp_client_name' => 'required|string|max:255',
            'temp_client_email' => 'nullable|email|max:255',
            'temp_client_phone' => 'nullable|string|max:50',
            'temp_client_company' => 'nullable|string|max:255',
            'temp_client_address' => 'nullable|string|max:500',
            'temp_client_tax_id' => 'nullable|string|max:50',
            'temp_client_registration_number' => 'nullable|string|max:100',
            'temp_client_bank_account' => 'nullable|string|max:100',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'temp_client_name.required' => __('validation.required', ['attribute' => __('Client name')]),
            'temp_client_name.max' => __('validation.max.string', ['attribute' => __('Client name'), 'max' => 255]),
            'temp_client_email.email' => __('validation.email', ['attribute' => __('Email')]),
        ];
    }
}
