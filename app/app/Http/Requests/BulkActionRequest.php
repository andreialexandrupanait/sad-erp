<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->organization_id !== null;
    }

    public function rules(): array
    {
        return [
            "ids" => "required|array|min:1|max:100",
            "ids.*" => "required|integer|min:1",
            "action" => "required|string",
        ];
    }

    public function messages(): array
    {
        return [
            "ids.required" => "Please select at least one item.",
            "ids.max" => "You can only select up to 100 items at once.",
            "ids.*.integer" => "Invalid item ID provided.",
        ];
    }
}
