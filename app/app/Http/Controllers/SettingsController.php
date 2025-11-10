<?php

namespace App\Http\Controllers;

use App\Models\SettingCategory;
use App\Models\SettingGroup;
use App\Models\SettingOption;
use App\Helpers\SettingsHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SettingsController extends Controller
{
    /**
     * Display the settings page with all categories and groups
     */
    public function index()
    {
        $categories = SettingCategory::with(['groups.options' => function ($query) {
            $query->orderBy('order');
        }])
        ->active()
        ->ordered()
        ->get();

        return view('settings.index', compact('categories'));
    }

    /**
     * Store a new option for a group
     */
    public function storeOption(Request $request, SettingGroup $group)
    {
        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'value' => 'required|string|max:255|unique:setting_options,value,NULL,id,group_id,' . $group->id,
            'color' => 'nullable|string|max:7',
            'description' => 'nullable|string',
            'is_default' => 'boolean',
        ]);

        $validated['group_id'] = $group->id;
        $validated['order'] = $group->options()->max('order') + 1;

        $option = SettingOption::create($validated);

        SettingsHelper::clearCache($group->key);

        return response()->json([
            'success' => true,
            'option' => $option
        ]);
    }

    /**
     * Update an existing option
     */
    public function updateOption(Request $request, SettingOption $option)
    {
        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'value' => 'required|string|max:255|unique:setting_options,value,' . $option->id . ',id,group_id,' . $option->group_id,
            'color' => 'nullable|string|max:7',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'order' => 'integer',
        ]);

        $option->update($validated);

        SettingsHelper::clearCache($option->group->key);

        return response()->json([
            'success' => true,
            'option' => $option->fresh()
        ]);
    }

    /**
     * Delete an option
     */
    public function deleteOption(SettingOption $option)
    {
        $groupKey = $option->group->key;
        $option->delete();

        SettingsHelper::clearCache($groupKey);

        return response()->json([
            'success' => true
        ]);
    }

    /**
     * Reorder options within a group
     */
    public function reorderOptions(Request $request, SettingGroup $group)
    {
        $validated = $request->validate([
            'options' => 'required|array',
            'options.*.id' => 'required|exists:setting_options,id',
            'options.*.order' => 'required|integer',
        ]);

        foreach ($validated['options'] as $optionData) {
            SettingOption::where('id', $optionData['id'])
                ->update(['order' => $optionData['order']]);
        }

        SettingsHelper::clearCache($group->key);

        return response()->json([
            'success' => true
        ]);
    }

    /**
     * Create a new setting group
     */
    public function storeGroup(Request $request, SettingCategory $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'has_colors' => 'boolean',
        ]);

        $validated['category_id'] = $category->id;
        $validated['slug'] = Str::slug($validated['name']);
        $validated['key'] = $category->slug . '_' . Str::slug($validated['name']);
        $validated['order'] = $category->groups()->max('order') + 1;

        $group = SettingGroup::create($validated);

        return response()->json([
            'success' => true,
            'group' => $group->load('options')
        ]);
    }

    /**
     * Update a setting group
     */
    public function updateGroup(Request $request, SettingGroup $group)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'has_colors' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $group->update($validated);

        SettingsHelper::clearCache($group->key);

        return response()->json([
            'success' => true,
            'group' => $group->fresh()
        ]);
    }

    /**
     * Delete a setting group
     */
    public function deleteGroup(SettingGroup $group)
    {
        $groupKey = $group->key;
        $group->delete();

        SettingsHelper::clearCache($groupKey);

        return response()->json([
            'success' => true
        ]);
    }
}
