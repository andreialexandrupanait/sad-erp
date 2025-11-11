<?php

namespace App\Http\Controllers;

use App\Models\ClientSetting;
use App\Http\View\Composers\SettingsComposer;
use Illuminate\Http\Request;

/**
 * Client Settings Controller
 *
 * Manages client-specific settings (statuses)
 */
class ClientSettingsController extends Controller
{
    /**
     * Store a new client status
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'label' => 'required|string|max:255', // Maps to 'name'
            'value' => 'nullable|string|max:255', // Not used, but accepted for compatibility
            'color' => 'required|string|max:7',
            'color_background' => 'nullable|string|max:7',
            'color_text' => 'nullable|string|max:7',
        ]);

        // Map 'label' to 'name'
        $validated['name'] = $validated['label'];
        unset($validated['label'], $validated['value']);

        // Auto-generate background and text colors if not provided
        if (!isset($validated['color_background'])) {
            $validated['color_background'] = $this->generateBackgroundColor($validated['color']);
        }

        if (!isset($validated['color_text'])) {
            $validated['color_text'] = $this->generateTextColor($validated['color']);
        }

        // Set order_index to be last
        $validated['order_index'] = ClientSetting::max('order_index') + 1;
        $validated['is_active'] = true;

        $setting = ClientSetting::create($validated);

        // Clear cache
        SettingsComposer::clearCache();

        return response()->json([
            'success' => true,
            'setting' => $setting
        ]);
    }

    /**
     * Update an existing client status
     */
    public function update(Request $request, ClientSetting $setting)
    {
        $validated = $request->validate([
            'label' => 'required|string|max:255', // Maps to 'name'
            'value' => 'nullable|string|max:255', // Not used, but accepted for compatibility
            'color' => 'required|string|max:7',
            'color_background' => 'nullable|string|max:7',
            'color_text' => 'nullable|string|max:7',
            'is_active' => 'boolean',
            'order_index' => 'integer',
        ]);

        // Map 'label' to 'name'
        $validated['name'] = $validated['label'];
        unset($validated['label'], $validated['value']);

        // Auto-generate background and text colors if not provided
        if (!isset($validated['color_background']) && isset($validated['color'])) {
            $validated['color_background'] = $this->generateBackgroundColor($validated['color']);
        }

        if (!isset($validated['color_text']) && isset($validated['color'])) {
            $validated['color_text'] = $this->generateTextColor($validated['color']);
        }

        $setting->update($validated);

        // Clear cache
        SettingsComposer::clearCache();

        return response()->json([
            'success' => true,
            'setting' => $setting->fresh()
        ]);
    }

    /**
     * Delete a client status
     */
    public function destroy(ClientSetting $setting)
    {
        $setting->delete();

        // Clear cache
        SettingsComposer::clearCache();

        return response()->json([
            'success' => true
        ]);
    }

    /**
     * Reorder client statuses
     */
    public function reorder(Request $request)
    {
        try {
            $validated = $request->validate([
                'settings' => 'required|array',
                'settings.*.id' => 'required|integer|exists:client_settings,id',
                'settings.*.order_index' => 'required|integer|min:0',
            ]);

            foreach ($validated['settings'] as $settingData) {
                ClientSetting::where('id', $settingData['id'])
                    ->where('user_id', auth()->id())
                    ->update(['order_index' => $settingData['order_index']]);
            }

            // Clear cache
            SettingsComposer::clearCache();

            return response()->json([
                'success' => true
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate a light background color from primary color
     */
    private function generateBackgroundColor(string $color): string
    {
        $hex = ltrim($color, '#');
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        // Lighten by mixing with white (90% white, 10% color)
        $r = round($r * 0.1 + 255 * 0.9);
        $g = round($g * 0.1 + 255 * 0.9);
        $b = round($b * 0.1 + 255 * 0.9);

        return sprintf('#%02X%02X%02X', $r, $g, $b);
    }

    /**
     * Generate a dark text color from primary color
     */
    private function generateTextColor(string $color): string
    {
        $hex = ltrim($color, '#');
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        // Darken by 60%
        $r = round($r * 0.4);
        $g = round($g * 0.4);
        $b = round($b * 0.4);

        return sprintf('#%02X%02X%02X', $r, $g, $b);
    }
}
