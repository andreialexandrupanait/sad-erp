<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\ApplicationSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AnthropicController extends Controller
{
    /**
     * Available Claude models.
     */
    public const MODELS = [
        'claude-sonnet-4-20250514' => 'Claude Sonnet 4 (Recommended)',
        'claude-3-5-sonnet-20241022' => 'Claude 3.5 Sonnet',
        'claude-3-haiku-20240307' => 'Claude 3 Haiku (Fast & Cheap)',
    ];

    /**
     * Show Anthropic integration settings page.
     */
    public function index()
    {
        $settings = [
            'anthropic_enabled' => ApplicationSetting::get('anthropic_enabled', false),
            'anthropic_api_key' => ApplicationSetting::get('anthropic_api_key', ''),
            'anthropic_model' => ApplicationSetting::get('anthropic_model', 'claude-sonnet-4-20250514'),
            'anthropic_max_tokens' => ApplicationSetting::get('anthropic_max_tokens', 8192),
        ];

        $models = self::MODELS;

        return view('settings.integrations.anthropic', compact('settings', 'models'));
    }

    /**
     * Update Anthropic settings.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'anthropic_enabled' => 'nullable|boolean',
            'anthropic_api_key' => 'nullable|required_if:anthropic_enabled,1|string|max:500',
            'anthropic_model' => 'nullable|string|in:' . implode(',', array_keys(self::MODELS)),
            'anthropic_max_tokens' => 'nullable|integer|min:1024|max:16384',
        ]);

        // Convert checkbox value
        $validated['anthropic_enabled'] = isset($validated['anthropic_enabled']) && $validated['anthropic_enabled'] == '1';

        // Save all settings
        ApplicationSetting::set('anthropic_enabled', $validated['anthropic_enabled'], 'boolean');

        if (!empty($validated['anthropic_api_key'])) {
            // Only update API key if provided (don't clear existing key with empty value)
            ApplicationSetting::set('anthropic_api_key', $validated['anthropic_api_key'], 'string');
        }

        if (!empty($validated['anthropic_model'])) {
            ApplicationSetting::set('anthropic_model', $validated['anthropic_model'], 'string');
        }

        if (!empty($validated['anthropic_max_tokens'])) {
            ApplicationSetting::set('anthropic_max_tokens', $validated['anthropic_max_tokens'], 'integer');
        }

        return redirect()->route('settings.anthropic.index')
            ->with('success', __('Claude AI settings updated successfully!'));
    }

    /**
     * Test Anthropic API connection.
     */
    public function test()
    {
        try {
            $apiKey = ApplicationSetting::get('anthropic_api_key');
            $model = ApplicationSetting::get('anthropic_model', 'claude-sonnet-4-20250514');

            if (empty($apiKey)) {
                return response()->json([
                    'success' => false,
                    'message' => __('API key not configured. Please save your API key first.')
                ], 400);
            }

            // Test the API with a simple request
            $response = Http::withHeaders([
                'x-api-key' => $apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])->timeout(30)->post('https://api.anthropic.com/v1/messages', [
                'model' => $model,
                'max_tokens' => 50,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => 'Say "API connection successful" in exactly those words.',
                    ],
                ],
            ]);

            if ($response->successful()) {
                $content = $response->json('content.0.text') ?? 'Connection successful';
                return response()->json([
                    'success' => true,
                    'message' => __('Connection successful! Model: :model', ['model' => $model]),
                    'response' => $content,
                ]);
            } else {
                $error = $response->json('error.message') ?? $response->body();
                return response()->json([
                    'success' => false,
                    'message' => __('API error: :error', ['error' => $error]),
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('Connection failed: :error', ['error' => $e->getMessage()]),
            ], 500);
        }
    }

    /**
     * Disconnect Anthropic integration.
     */
    public function disconnect()
    {
        ApplicationSetting::set('anthropic_enabled', false, 'boolean');
        ApplicationSetting::set('anthropic_api_key', '', 'string');

        return redirect()->route('settings.anthropic.index')
            ->with('success', __('Claude AI integration disconnected.'));
    }
}
