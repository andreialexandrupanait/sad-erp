<?php

namespace App\Http\Requests\Domain;

use App\Models\SettingOption;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDomainRequest extends FormRequest
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
            'domain_name' => ['required', 'string', 'max:255', 'unique:domains,domain_name'],
            'client_id' => ['nullable', 'exists:clients,id'],
            'registrar' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in($this->getValidStatuses())],
            'registration_date' => ['nullable', 'date'],
            'expiry_date' => ['required', 'date', 'after_or_equal:registration_date'],
            'annual_cost' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'auto_renew' => ['boolean'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'auto_renew' => $this->has('auto_renew'),
            'domain_name' => $this->domain_name ? strtolower(trim($this->domain_name)) : null,
        ]);
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'domain_name' => __('domain name'),
            'client_id' => __('client'),
            'registrar' => __('registrar'),
            'status' => __('status'),
            'registration_date' => __('registration date'),
            'expiry_date' => __('expiry date'),
            'annual_cost' => __('annual cost'),
            'auto_renew' => __('auto renew'),
            'notes' => __('notes'),
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'domain_name.required' => __('Please enter a domain name.'),
            'domain_name.unique' => __('This domain already exists.'),
            'domain_name.max' => __('The domain name cannot exceed :max characters.'),
            'status.required' => __('Please select a status.'),
            'status.in' => __('The selected status is not valid.'),
            'expiry_date.required' => __('Please enter an expiry date.'),
            'expiry_date.after_or_equal' => __('The expiry date must be on or after the registration date.'),
            'annual_cost.numeric' => __('The annual cost must be a valid number.'),
            'annual_cost.min' => __('The annual cost cannot be negative.'),
        ];
    }

    /**
     * Get valid statuses from settings.
     */
    protected function getValidStatuses(): array
    {
        return SettingOption::domainStatuses()->pluck('value')->toArray();
    }
}
