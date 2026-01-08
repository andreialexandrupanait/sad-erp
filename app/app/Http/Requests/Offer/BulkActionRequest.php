<?php

namespace App\Http\Requests\Offer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'action' => ['required', 'string', Rule::in(['export', 'delete', 'status_change'])],
            'offer_ids' => ['required_unless:action,export', 'array'],
            'offer_ids.*' => ['integer', 'exists:offers,id'],
            'new_status' => ['required_if:action,status_change', 'string', Rule::in(['draft', 'expired'])],
            'format' => ['nullable', 'string', Rule::in(['xlsx', 'csv'])],
            'export_all' => ['nullable', 'boolean'],
            'status_filter' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'offer_ids.required_unless' => __('Please select at least one offer.'),
            'new_status.required_if' => __('Please select a new status.'),
        ];
    }
}
