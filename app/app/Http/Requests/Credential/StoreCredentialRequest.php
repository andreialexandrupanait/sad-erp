<?php

namespace App\Http\Requests\Credential;

use App\Models\Credential;
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
        $validTypes = array_keys(Credential::CREDENTIAL_TYPES);

        return [
            'site_name' => 'required|string|max:255',
            'platform' => ['required', Rule::in($validPlatforms)],
            'username' => 'required|string|max:255',
            // Security: Limit to printable ASCII only (space through tilde)
            // This prevents control characters and null bytes in passwords
            'password' => ['required', 'string', 'min:1', 'max:255', 'regex:/^[\x20-\x7E]*$/'],
            'url' => 'nullable|url|max:255',
            'client_id' => 'nullable|exists:clients,id',
            'credential_type' => ['nullable', Rule::in($validTypes)],
            'notes' => 'nullable|string',
        ];
    }
}
