<?php

namespace App\Http\Controllers;

use App\Models\SettingCategory;
use App\Models\SettingGroup;
use App\Models\SettingOption;
use App\Models\ApplicationSetting;
use App\Models\ClientSetting;
use App\Helpers\SettingsHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

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

        // Load module-specific settings (from *_settings tables)
        // These override the generic setting_options for specific modules
        $clientStatuses = ClientSetting::active()->ordered()->get();

        // Get application settings
        $appSettings = [
            'app_name' => ApplicationSetting::get('app_name', 'ERP System'),
            'app_logo' => ApplicationSetting::get('app_logo'),
            'app_favicon' => ApplicationSetting::get('app_favicon'),
            'theme_mode' => ApplicationSetting::get('theme_mode', 'light'),
            'primary_color' => ApplicationSetting::get('primary_color', '#3b82f6'),
            'language' => ApplicationSetting::get('language', 'ro'),
            'timezone' => ApplicationSetting::get('timezone', 'Europe/Bucharest'),
            'date_format' => ApplicationSetting::get('date_format', 'd/m/Y'),
        ];

        return view('settings.index', compact('categories', 'appSettings', 'clientStatuses'));
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
        try {
            $validated = $request->validate([
                'options' => 'required|array',
                'options.*.id' => 'required|integer|exists:setting_options,id',
                'options.*.order' => 'required|integer|min:0',
            ]);

            foreach ($validated['options'] as $optionData) {
                SettingOption::where('id', $optionData['id'])
                    ->where('group_id', $group->id) // Ensure option belongs to this group
                    ->update(['order' => $optionData['order']]);
            }

            SettingsHelper::clearCache($group->key);

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

    /**
     * Update application settings
     */
    public function updateApplicationSettings(Request $request)
    {
        $validated = $request->validate([
            'app_name' => 'required|string|max:255',
            'theme_mode' => 'required|in:light,dark,auto',
            'primary_color' => 'required|string|max:7',
            'language' => 'required|string|max:5',
            'timezone' => 'required|string|max:50',
            'date_format' => 'required|string|max:20',
            'app_logo' => 'nullable|image|max:2048',
            'app_favicon' => 'nullable|image|max:1024',
        ]);

        // Handle file uploads
        if ($request->hasFile('app_logo')) {
            // Get old logo before uploading new one
            $oldLogo = ApplicationSetting::get('app_logo');

            // Upload new logo
            $logoPath = $request->file('app_logo')->store('app-settings', 'public');
            ApplicationSetting::set('app_logo', $logoPath, 'file');

            // Delete old logo if exists
            if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }
        }

        if ($request->hasFile('app_favicon')) {
            // Get old favicon before uploading new one
            $oldFavicon = ApplicationSetting::get('app_favicon');

            // Upload new favicon
            $faviconPath = $request->file('app_favicon')->store('app-settings', 'public');
            ApplicationSetting::set('app_favicon', $faviconPath, 'file');

            // Delete old favicon if exists
            if ($oldFavicon && Storage::disk('public')->exists($oldFavicon)) {
                Storage::disk('public')->delete($oldFavicon);
            }
        }

        // Update other settings
        ApplicationSetting::set('app_name', $validated['app_name']);
        ApplicationSetting::set('theme_mode', $validated['theme_mode']);
        ApplicationSetting::set('primary_color', $validated['primary_color']);
        ApplicationSetting::set('language', $validated['language']);
        ApplicationSetting::set('timezone', $validated['timezone']);
        ApplicationSetting::set('date_format', $validated['date_format']);

        return redirect()->route('settings.index')->with('success', 'Application settings updated successfully!');
    }
}
