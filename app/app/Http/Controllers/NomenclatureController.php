<?php

namespace App\Http\Controllers;

use App\Models\SettingOption;
use App\Http\View\Composers\SettingsComposer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Nomenclature Controller
 *
 * Manages all nomenclature settings (statuses, categories, etc.) in settings_options table
 */
class NomenclatureController extends Controller
{
    /**
     * Valid nomenclature categories
     */
    protected array $validCategories = [
        'client_statuses',
        'domain_statuses',
        'subscription_statuses',
        'access_platforms',
        'expense_categories',
        'payment_methods',
        'billing_cycles',
        'domain_registrars',
        'currencies',
        'dashboard_quick_actions',
    ];

    /**
     * Categories that support colors
     */
    protected array $categoriesWithColors = [
        'client_statuses',
        'domain_statuses',
        'subscription_statuses',
        'access_platforms',
        'expense_categories',
        'payment_methods',
        'billing_cycles',
        'domain_registrars',
        'currencies',
        'dashboard_quick_actions',
    ];

    /**
     * Store a new nomenclature option
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required|string|in:' . implode(',', $this->validCategories),
            'label' => 'required|string|max:255',
            'value' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:7',
            'parent_id' => 'nullable|integer|exists:settings_options,id',
        ]);

        $category = $validated['category'];

        // Auto-generate value from label if not provided
        $value = $validated['value'] ?? Str::slug($validated['label']);

        // Get max sort_order for this category and parent level
        $query = SettingOption::where('category', $category);

        // If parent_id is provided, get max sort_order among siblings
        if (isset($validated['parent_id'])) {
            $query->where('parent_id', $validated['parent_id']);
        } else {
            // Get max sort_order among root level items
            $query->whereNull('parent_id');
        }

        $maxOrder = $query->max('sort_order') ?? 0;

        $data = [
            'category' => $category,
            'label' => $validated['label'],
            'value' => $value,
            'sort_order' => $maxOrder + 1,
            'is_active' => true,
            'is_default' => false,
            'parent_id' => $validated['parent_id'] ?? null,
        ];

        // Add color if category supports it and color is provided
        if (in_array($category, $this->categoriesWithColors) && isset($validated['color'])) {
            $data['color_class'] = $validated['color'];
        }

        $setting = SettingOption::create($data);

        // Clear cache
        SettingsComposer::clearCache();

        return response()->json([
            'success' => true,
            'setting' => $setting
        ]);
    }

    /**
     * Update an existing nomenclature option
     */
    public function update(Request $request, SettingOption $setting)
    {
        // Validate category is valid
        if (!in_array($setting->category, $this->validCategories)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid setting category'
            ], 400);
        }

        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'value' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:7',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'parent_id' => 'nullable|integer|exists:settings_options,id',
        ]);

        // Auto-generate value from label if not provided
        if (empty($validated['value'])) {
            $validated['value'] = Str::slug($validated['label']);
        }

        // Prevent setting itself as parent
        if (isset($validated['parent_id']) && $validated['parent_id'] == $setting->id) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot set category as its own parent'
            ], 400);
        }

        // Prevent setting a child as parent (circular reference)
        if (isset($validated['parent_id'])) {
            $childIds = $setting->children()->pluck('id')->toArray();
            if (in_array($validated['parent_id'], $childIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot set a subcategory as parent'
                ], 400);
            }
        }

        // Map color to color_class for categories that support colors
        if (in_array($setting->category, $this->categoriesWithColors) && isset($validated['color'])) {
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
     * Delete a nomenclature option
     */
    public function destroy(SettingOption $setting)
    {
        // Validate category is valid
        if (!in_array($setting->category, $this->validCategories)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid setting category'
            ], 400);
        }

        // Check if this category has children
        if ($setting->children()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete category with subcategories. Please delete subcategories first.'
            ], 400);
        }

        // Force delete (permanent removal from database)
        $setting->forceDelete();

        // Clear cache
        SettingsComposer::clearCache();

        return response()->json([
            'success' => true
        ]);
    }

    /**
     * Reorder nomenclature options for a specific category
     */
    public function reorder(Request $request)
    {
        try {
            $validated = $request->validate([
                'items' => 'required|array',
                'items.*.id' => 'required|integer|exists:settings_options,id',
                'items.*.sort_order' => 'required|integer|min:1',
            ]);

            // Use Spatie's setNewOrder method for cleaner implementation
            $ids = collect($validated['items'])
                ->sortBy('sort_order')
                ->pluck('id')
                ->toArray();

            SettingOption::setNewOrder($ids);

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
