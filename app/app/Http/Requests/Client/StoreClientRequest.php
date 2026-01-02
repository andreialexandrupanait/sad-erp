<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClientRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->organization_id !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'tax_id' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('clients')->where(function ($query) {
                    $taxId = $this->input('tax_id');
                    if ($taxId !== null && $taxId !== '' && $taxId !== '-') {
                        return $query->where('organization_id', auth()->user()->organization_id)
                                     ->where('tax_id', '!=', '-')
                                     ->whereNotNull('tax_id');
                    }
                    return $query->whereRaw('1 = 0');
                }),
            ],
            'registration_number' => ['nullable', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'vat_payer' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
            'status_id' => ['nullable', 'exists:settings_options,id'],
            'order_index' => ['nullable', 'integer'],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Handle vat_payer from both form checkbox and JSON API requests
        // Checkbox sends nothing when unchecked, JSON sends '0' or '1'
        $vatPayer = $this->input('vat_payer');

        $this->merge([
            'vat_payer' => filter_var($vatPayer, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
        ]);
    }

    /**
     * Get the validated data with sanitization applied.
     */
    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated($key, $default);

        // Sanitize text inputs to prevent XSS
        $validated['name'] = sanitize_input($validated['name']);

        if (!empty($validated['company_name'])) {
            $validated['company_name'] = sanitize_input($validated['company_name']);
        }

        if (!empty($validated['contact_person'])) {
            $validated['contact_person'] = sanitize_input($validated['contact_person']);
        }

        return $validated;
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => __('name'),
            'company_name' => __('company name'),
            'tax_id' => __('tax ID'),
            'registration_number' => __('registration number'),
            'contact_person' => __('contact person'),
            'email' => __('email'),
            'phone' => __('phone'),
            'address' => __('address'),
            'vat_payer' => __('VAT payer'),
            'notes' => __('notes'),
            'status_id' => __('status'),
            'order_index' => __('order'),
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => __('Please enter a client name.'),
            'name.max' => __('The name cannot exceed :max characters.'),
            'email.email' => __('Please enter a valid email address.'),
            'tax_id.unique' => __('This tax ID is already in use.'),
            'status_id.exists' => __('The selected status is not valid.'),
        ];
    }
}
