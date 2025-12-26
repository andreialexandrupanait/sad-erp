<?php

namespace App\Http\Requests\Offer;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOfferRequest extends FormRequest
{
    public function authorize(): bool
    {
        $offer = $this->route('offer');

        // Check organization ownership (security: user can only edit their own organization's offers)
        if (auth()->check() && $offer->organization_id !== auth()->user()->organization_id) {
            return false;
        }

        return $offer->canBeEdited();
    }

    public function rules(): array
    {
        // Allow null client_id when using temp client fields
        $hasTempClient = $this->filled('temp_client_name') || $this->filled('temp_client_company');

        return [
            'client_id' => $hasTempClient ? 'nullable' : 'required|exists:clients,id',
            // Temp client fields (alternative to client_id)
            'temp_client_name' => 'nullable|string|max:255',
            'temp_client_email' => 'nullable|email|max:255',
            'temp_client_phone' => 'nullable|string|max:50',
            'temp_client_company' => 'nullable|string|max:255',
            'header_data' => 'nullable|array',
            'template_id' => 'nullable|exists:document_templates,id',
            'contract_id' => 'nullable|exists:contracts,id',
            'title' => 'nullable|string|max:255',
            'introduction' => 'nullable|string',
            'terms' => 'nullable|string',
            'blocks' => 'nullable|array',
            'valid_until' => 'required|date|after:today',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'currency' => 'required|string|size:3',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.id' => 'nullable|exists:offer_items,id',
            'items.*.title' => 'required|string|max:255',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit' => 'nullable|string',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.currency' => 'nullable|string|size:3',
            'items.*.is_recurring' => 'boolean',
            'items.*.billing_cycle' => 'nullable|string',
            'items.*.custom_cycle_days' => 'nullable|integer|min:1',
            'items.*.service_id' => 'nullable|exists:services,id',
            'items.*.type' => 'nullable|string|in:custom,card',
            'items.*.is_selected' => 'nullable|boolean',
            'items.*.discount_percent' => 'nullable|numeric|min:0|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'client_id.required' => __('Please select a client.'),
            'title.required' => __('Please enter an offer title.'),
            'valid_until.required' => __('Please set an expiration date.'),
            'valid_until.after' => __('The expiration date must be in the future.'),
            'items.required' => __('Please add at least one service item.'),
            'items.min' => __('Please add at least one service item.'),
            'items.*.title.required' => __('Each item must have a title.'),
            'items.*.quantity.required' => __('Each item must have a quantity.'),
            'items.*.unit_price.required' => __('Each item must have a price.'),
        ];
    }

    protected function failedAuthorization(): void
    {
        throw new \Illuminate\Auth\Access\AuthorizationException(__('This offer cannot be edited.'));
    }
}
