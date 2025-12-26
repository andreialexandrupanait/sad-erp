<?php

namespace App\Http\Requests\Credential;

use App\Models\Credential;
use App\Models\SettingOption;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCredentialRequest extends FormRequest
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
        $validPlatforms = SettingOption::accessPlatforms()->pluck('value')->toArray();
        $validTypes = array_keys(Credential::CREDENTIAL_TYPES);

        return [
            'site_name' => 'required|string|max:255',
            'platform' => ['required', Rule::in($validPlatforms)],
            'username' => 'required|string|max:255',
            'password' => 'nullable|string|max:500', // Optional on update
            'url' => 'nullable|url|max:255',
            'client_id' => 'nullable|exists:clients,id',
            'credential_type' => ['nullable', Rule::in($validTypes)],
            'notes' => 'nullable|string',
        ];
    }
}
