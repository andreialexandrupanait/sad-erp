<?php

namespace App\Http\Requests\Nomenclature;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNomenclatureRequest extends FormRequest
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
            'label' => 'required|string|max:255',
            'value' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:7',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'parent_id' => 'nullable|integer|exists:settings_options,id',
        ];
    }
}
