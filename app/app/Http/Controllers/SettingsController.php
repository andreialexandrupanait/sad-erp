<?php

namespace App\Http\Controllers;

use App\Models\ApplicationSetting;
use App\Models\SettingOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    /**
     * Display the settings page with module-specific settings
     */
    public function index()
    {
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

        // Load all settings from unified settings_options table
        $clientStatuses = SettingOption::clientStatuses()->get();
        $domainRegistrars = SettingOption::domainRegistrars()->get();
        $domainStatuses = SettingOption::domainStatuses()->get();
        $subscriptionBillingCycles = SettingOption::billingCycles()->get();
        $subscriptionStatuses = SettingOption::subscriptionStatuses()->get();
        $accessPlatforms = SettingOption::accessPlatforms()->get();
        $paymentMethods = SettingOption::paymentMethods()->get();

        // Load expense categories
        $expenseCategories = SettingOption::rootCategories()->active()->ordered()->get();

        // Empty categories for backwards compatibility with view
        $categories = collect([]);

        return view('settings.index', compact(
            'appSettings',
            'clientStatuses',
            'domainRegistrars',
            'domainStatuses',
            'subscriptionBillingCycles',
            'subscriptionStatuses',
            'accessPlatforms',
            'paymentMethods',
            'expenseCategories',
            'categories'
        ));
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
