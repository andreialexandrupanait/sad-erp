<?php

namespace App\Http\Requests\Nomenclature;

use App\Models\SettingOption;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreNomenclatureRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Valid nomenclature categories.
     */
    protected array $validCategories = [
        'client_statuses',
        'domain_statuses',
        'subscription_statuses',
        'access_platforms',
        'expense_categories',
        'payment_methods',
        'billing_cycles',
        'domain_registrars',
        'currencies',
        'dashboard_quick_actions',
    ];

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'category' => 'required|string|in:' . implode(',', $this->validCategories),
            'label' => [
                'required',
                'string',
                'max:255',
                Rule::unique('settings_options', 'label')
                    ->where('category', $this->input('category'))
                    ->whereNull('deleted_at'),
            ],
            'value' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:7',
            'parent_id' => 'nullable|integer|exists:settings_options,id',
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'label.unique' => 'Această opțiune există deja în această categorie.',
        ];
    }
}
