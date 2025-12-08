<?php

namespace App\Http\Requests\Subscription;

use App\Models\SettingOption;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSubscriptionRequest extends FormRequest
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
            'vendor_name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'currency' => ['required', Rule::in($this->getValidCurrencies())],
            'billing_cycle' => ['required', Rule::in($this->getValidBillingCycles())],
            'custom_days' => ['nullable', 'integer', 'min:1', 'max:3650', 'required_if:billing_cycle,custom'],
            'start_date' => ['required', 'date'],
            'next_renewal_date' => ['required', 'date', 'after_or_equal:start_date'],
            'status' => ['required', Rule::in($this->getValidStatuses())],
            'notes' => ['nullable', 'string'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'vendor_name' => __('vendor name'),
            'price' => __('price'),
            'currency' => __('currency'),
            'billing_cycle' => __('billing cycle'),
            'custom_days' => __('custom days'),
            'start_date' => __('start date'),
            'next_renewal_date' => __('next renewal date'),
            'status' => __('status'),
            'notes' => __('notes'),
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'vendor_name.required' => __('Please enter a vendor name.'),
            'vendor_name.max' => __('The vendor name cannot exceed :max characters.'),
            'price.required' => __('Please enter a price.'),
            'price.numeric' => __('The price must be a valid number.'),
            'price.min' => __('The price cannot be negative.'),
            'currency.required' => __('Please select a currency.'),
            'currency.in' => __('The selected currency is not valid.'),
            'billing_cycle.required' => __('Please select a billing cycle.'),
            'billing_cycle.in' => __('The selected billing cycle is not valid.'),
            'custom_days.required_if' => __('Please enter the number of days for custom billing cycle.'),
            'custom_days.min' => __('Custom days must be at least 1.'),
            'custom_days.max' => __('Custom days cannot exceed 3650 (10 years).'),
            'start_date.required' => __('Please enter a start date.'),
            'next_renewal_date.required' => __('Please enter a next renewal date.'),
            'next_renewal_date.after_or_equal' => __('The next renewal date must be on or after the start date.'),
            'status.required' => __('Please select a status.'),
            'status.in' => __('The selected status is not valid.'),
        ];
    }

    /**
     * Get valid billing cycles from settings.
     */
    protected function getValidBillingCycles(): array
    {
        return SettingOption::billingCycles()->pluck('value')->toArray();
    }

    /**
     * Get valid statuses from settings.
     */
    protected function getValidStatuses(): array
    {
        return SettingOption::subscriptionStatuses()->pluck('value')->toArray();
    }

    /**
     * Get valid currencies from settings.
     */
    protected function getValidCurrencies(): array
    {
        return SettingOption::currencies()->pluck('value')->toArray();
    }
}
