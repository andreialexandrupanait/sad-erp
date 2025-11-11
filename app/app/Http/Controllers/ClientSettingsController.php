<?php

namespace App\Http\Controllers;

use App\Models\SettingOption;
use App\Http\View\Composers\SettingsComposer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Client Settings Controller
 *
 * Manages client-specific settings (statuses) in settings_options table
 */
class ClientSettingsController extends Controller
{
    /**
     * Store a new client status
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'value' => 'nullable|string|max:255',
            'color' => 'required|string|max:7',
            'color_background' => 'nullable|string|max:7',
            'color_text' => 'nullable|string|max:7',
        ]);

        // Auto-generate value from label if not provided
        $value = $validated['value'] ?? Str::slug($validated['label']);

        // Get max sort_order for client_status category
        $maxOrder = SettingOption::where('category', 'client_statuses')->max('sort_order') ?? 0;

        $setting = SettingOption::create([
            'category' => 'client_statuses',
            'label' => $validated['label'],
            'value' => $value,
            'color_class' => $validated['color'],
            'sort_order' => $maxOrder + 1,
            'is_active' => true,
            'is_default' => false,
        ]);

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
    public function update(Request $request, SettingOption $setting)
    {
        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'value' => 'nullable|string|max:255',
            'color' => 'required|string|max:7',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        // Auto-generate value from label if not provided
        if (isset($validated['label']) && !isset($validated['value'])) {
            $validated['value'] = Str::slug($validated['label']);
        }

        // Map color to color_class
        if (isset($validated['color'])) {
            $validated['color_class'] = $validated['color'];
            unset($validated['color']);
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
    public function destroy(SettingOption $setting)
    {
        // Ensure we're only deleting client_status options
        if ($setting->category !== 'client_statuses') {
            return response()->json([
                'success' => false,
                'message' => 'Invalid setting type'
            ], 400);
        }

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
                'settings.*.id' => 'required|integer|exists:settings_options,id',
                'settings.*.sort_order' => 'required|integer|min:0',
            ]);

            foreach ($validated['settings'] as $settingData) {
                SettingOption::where('id', $settingData['id'])
                    ->where('category', 'client_statuses')
                    ->update(['sort_order' => $settingData['sort_order']]);
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
}
