<?php

namespace App\Http\Controllers;

use App\Models\ApplicationSetting;
use App\Models\SettingOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    /**
     * Display the application settings page
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

        return view('settings.application', compact('appSettings'));
    }

    /**
     * Display client statuses settings
     */
    public function clientStatuses()
    {
        $data = SettingOption::clientStatuses()->get();
        return view('settings.nomenclature', [
            'category' => 'client_statuses',
            'title' => 'Status clienti',
            'description' => 'Gestioneaza statusurile clientilor folosite in aplicatie',
            'data' => $data,
            'hasColors' => true,
        ]);
    }

    /**
     * Display domain statuses settings
     */
    public function domainStatuses()
    {
        $data = SettingOption::domainStatuses()->get();
        return view('settings.nomenclature', [
            'category' => 'domain_statuses',
            'title' => 'Status domenii',
            'description' => 'Gestioneaza statusurile domeniilor',
            'data' => $data,
            'hasColors' => true,
        ]);
    }

    /**
     * Display subscription statuses settings
     */
    public function subscriptionStatuses()
    {
        $data = SettingOption::subscriptionStatuses()->get();
        return view('settings.nomenclature', [
            'category' => 'subscription_statuses',
            'title' => 'Status abonamente',
            'description' => 'Gestioneaza statusurile abonamentelor',
            'data' => $data,
            'hasColors' => true,
        ]);
    }

    /**
     * Display access platforms settings
     */
    public function accessPlatforms()
    {
        $data = SettingOption::accessPlatforms()->get();
        return view('settings.nomenclature', [
            'category' => 'access_platforms',
            'title' => 'Categorii platforme',
            'description' => 'Gestioneaza tipurile de platforme de acces',
            'data' => $data,
            'hasColors' => true,
        ]);
    }

    /**
     * Display expense categories settings
     */
    public function expenseCategories()
    {
        $data = SettingOption::rootCategories()->with('children')->active()->ordered()->get();
        return view('settings.nomenclature', [
            'category' => 'expense_categories',
            'title' => 'Categorii cheltuieli',
            'description' => 'Gestioneaza categoriile de cheltuieli',
            'data' => $data,
            'hasColors' => true,
            'isHierarchical' => true,
        ]);
    }

    /**
     * Display payment methods settings
     */
    public function paymentMethods()
    {
        $data = SettingOption::paymentMethods()->get();
        return view('settings.nomenclature', [
            'category' => 'payment_methods',
            'title' => 'Metode de plata',
            'description' => 'Gestioneaza metodele de plata disponibile',
            'data' => $data,
            'hasColors' => true,
        ]);
    }

    /**
     * Display billing cycles settings
     */
    public function billingCycles()
    {
        $data = SettingOption::billingCycles()->get();
        return view('settings.nomenclature', [
            'category' => 'billing_cycles',
            'title' => 'Cicluri de facturare',
            'description' => 'Gestioneaza ciclurile de facturare pentru abonamente',
            'data' => $data,
            'hasColors' => true,
        ]);
    }

    /**
     * Display domain registrars settings
     */
    public function domainRegistrars()
    {
        $data = SettingOption::domainRegistrars()->get();
        return view('settings.nomenclature', [
            'category' => 'domain_registrars',
            'title' => 'Registratori de domenii',
            'description' => 'Gestioneaza registratorii de domenii',
            'data' => $data,
            'hasColors' => true,
        ]);
    }

    /**
     * Display currencies settings
     */
    public function currencies()
    {
        $data = SettingOption::currencies()->get();
        return view('settings.nomenclature', [
            'category' => 'currencies',
            'title' => 'Valute',
            'description' => 'Gestioneaza valutele disponibile in aplicatie',
            'data' => $data,
            'hasColors' => true,
        ]);
    }

    /**
     * Display dashboard quick actions settings
     */
    public function quickActions()
    {
        $data = SettingOption::dashboardQuickActions()->get();
        return view('settings.nomenclature', [
            'category' => 'dashboard_quick_actions',
            'title' => 'Quick Actions Dashboard',
            'description' => 'Gestioneaza butoanele de actiune rapida de pe dashboard',
            'data' => $data,
            'hasColors' => true,
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
