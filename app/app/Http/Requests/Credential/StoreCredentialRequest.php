<?php

namespace App\Http\Requests\Credential;

use App\Models\SettingOption;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCredentialRequest extends FormRequest
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
        $validPlatforms = SettingOption::accessPlatforms()->pluck('value')->toArray();

        return [
            'client_id' => 'required|exists:clients,id',
            'platform' => ['required', Rule::in($validPlatforms)],
            'url' => 'nullable|url|max:255',
            'username' => 'nullable|string|max:255',
            'password' => 'nullable|string|max:500',
            'notes' => 'nullable|string',
        ];
    }
}
