<?php

namespace App\Http\Requests\ClientNote;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientNoteRequest extends FormRequest
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
            'client_id' => ['nullable', 'exists:clients,id'],
            'content' => ['required', 'string', 'min:1', 'max:65535'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:50'],
        ];
    }

    /**
     * Get the validated data from the request and sanitize it.
     */
    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated($key, $default);

        // Sanitize content for XSS while preserving TinyMCE formatting
        if (isset($validated['content']) && function_exists('sanitize_html')) {
            $allowedTags = [
                'p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'strike',
                'ul', 'ol', 'li', 'a', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
                'blockquote', 'pre', 'code', 'hr', 'span', 'div',
                'table', 'thead', 'tbody', 'tr', 'td', 'th',
                'sub', 'sup',
            ];
            $validated['content'] = sanitize_html($validated['content'], $allowedTags);
        }

        return $validated;
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'client_id' => __('client'),
            'content' => __('content'),
            'tags' => __('tags'),
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'client_id.exists' => __('The selected client does not exist.'),
            'content.required' => __('Please enter the note content.'),
            'content.min' => __('The note content cannot be empty.'),
        ];
    }
}
