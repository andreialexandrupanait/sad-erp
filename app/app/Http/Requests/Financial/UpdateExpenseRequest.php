<?php

namespace App\Http\Requests\Financial;

use App\Models\SettingOption;
use App\Rules\SecureFileUpload;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateExpenseRequest extends FormRequest
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
        $validCurrencies = SettingOption::currencies()->pluck('value')->toArray();

        return [
            'document_name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'amount_eur' => 'nullable|numeric|min:0',
            'exchange_rate' => 'nullable|numeric|min:0',
            'currency' => ['required', Rule::in($validCurrencies)],
            'occurred_at' => 'required|date',
            'category_option_id' => 'nullable|exists:settings_options,id',
            'note' => 'nullable|string',
            'files' => 'nullable|array',
            'files.*' => ['file', new SecureFileUpload()],
            'delete_files' => 'nullable|array',
            'delete_files.*' => 'integer|exists:financial_files,id',
        ];
    }
}
