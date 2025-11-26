<?php

namespace App\Http\Controllers;

use App\Models\ApplicationSetting;
use App\Models\SettingOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    /**
     * Display the main settings hub page - redirects to Application
     */
    public function index()
    {
        return redirect()->route('settings.application');
    }

    /**
     * Display the application settings page
     */
    public function application()
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
     * Display the business settings hub page
     */
    public function business()
    {
        return view('settings.business.index');
    }

    /**
     * Display the integrations hub page
     */
    public function integrations()
    {
        return view('settings.integrations.index');
    }

    /**
     * Display the nomenclature hub page
     */
    public function nomenclatureIndex()
    {
        $counts = [
            'client_statuses' => SettingOption::clientStatuses()->count(),
            'domain_statuses' => SettingOption::domainStatuses()->count(),
            'subscription_statuses' => SettingOption::subscriptionStatuses()->count(),
            'access_platforms' => SettingOption::accessPlatforms()->count(),
            'expense_categories' => SettingOption::where('category', 'expense_categories')->count(),
            'payment_methods' => SettingOption::paymentMethods()->count(),
            'billing_cycles' => SettingOption::billingCycles()->count(),
            'currencies' => SettingOption::currencies()->count(),
            'domain_registrars' => SettingOption::domainRegistrars()->count(),
        ];

        return view('settings.nomenclature.index', compact('counts'));
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

    /**
     * Business Information Settings (Placeholder)
     */
    public function businessInfo()
    {
        return view('settings.coming-soon', [
            'title' => 'Business Information',
            'description' => 'Configure your company details, VAT number, address, and contact information.'
        ]);
    }

    /**
     * Invoice Settings (Placeholder)
     */
    public function invoiceSettings()
    {
        return view('settings.coming-soon', [
            'title' => 'Invoice Settings',
            'description' => 'Configure invoice numbering, tax rates, payment terms, and invoice templates.'
        ]);
    }

    /**
     * Notification Settings
     */
    public function notifications()
    {
        $notificationSettings = [
            // Master toggle
            'notifications_enabled' => ApplicationSetting::get('notifications_enabled', false),

            // Individual notifications
            'notify_domain_expiry' => ApplicationSetting::get('notify_domain_expiry', true),
            'notify_subscription_renewal' => ApplicationSetting::get('notify_subscription_renewal', true),
            'notify_new_client' => ApplicationSetting::get('notify_new_client', false),
            'notify_payment_received' => ApplicationSetting::get('notify_payment_received', false),
            'notify_monthly_summary' => ApplicationSetting::get('notify_monthly_summary', false),

            // Timing
            'domain_expiry_days_before' => ApplicationSetting::get('domain_expiry_days_before', 30),
            'subscription_renewal_days' => ApplicationSetting::get('subscription_renewal_days', 7),
            'monthly_summary_day' => ApplicationSetting::get('monthly_summary_day', 1),

            // Recipients
            'notification_email_primary' => ApplicationSetting::get('notification_email_primary', auth()->user()->email ?? ''),
            'notification_email_cc' => ApplicationSetting::get('notification_email_cc', ''),

            // SMTP
            'smtp_enabled' => ApplicationSetting::get('smtp_enabled', false),
            'smtp_host' => ApplicationSetting::get('smtp_host', ''),
            'smtp_port' => ApplicationSetting::get('smtp_port', 587),
            'smtp_username' => ApplicationSetting::get('smtp_username', ''),
            'smtp_password' => ApplicationSetting::get('smtp_password', ''),
            'smtp_encryption' => ApplicationSetting::get('smtp_encryption', 'tls'),
            'smtp_from_email' => ApplicationSetting::get('smtp_from_email', ''),
            'smtp_from_name' => ApplicationSetting::get('smtp_from_name', ApplicationSetting::get('app_name', 'ERP System')),
        ];

        return view('settings.notifications', compact('notificationSettings'));
    }

    /**
     * Update Notification Settings
     */
    public function updateNotifications(Request $request)
    {
        $validated = $request->validate([
            'notifications_enabled' => 'nullable|boolean',
            'notify_domain_expiry' => 'nullable|boolean',
            'notify_subscription_renewal' => 'nullable|boolean',
            'notify_new_client' => 'nullable|boolean',
            'notify_payment_received' => 'nullable|boolean',
            'notify_monthly_summary' => 'nullable|boolean',
            'domain_expiry_days_before' => 'required|integer|min:1|max:365',
            'subscription_renewal_days' => 'required|integer|min:1|max:90',
            'monthly_summary_day' => 'required|integer|min:1|max:28',
            'notification_email_primary' => 'required|email|max:255',
            'notification_email_cc' => 'nullable|string|max:500',
            'smtp_enabled' => 'nullable|boolean',
            'smtp_host' => 'nullable|required_if:smtp_enabled,1|string|max:255',
            'smtp_port' => 'nullable|required_if:smtp_enabled,1|integer|min:1|max:65535',
            'smtp_username' => 'nullable|required_if:smtp_enabled,1|string|max:255',
            'smtp_password' => 'nullable|string|max:255',
            'smtp_encryption' => 'nullable|string|in:tls,ssl,none',
            'smtp_from_email' => 'nullable|required_if:smtp_enabled,1|email|max:255',
            'smtp_from_name' => 'nullable|string|max:255',
        ]);

        // Convert checkbox values (null means unchecked = false)
        $booleanFields = [
            'notifications_enabled',
            'notify_domain_expiry',
            'notify_subscription_renewal',
            'notify_new_client',
            'notify_payment_received',
            'notify_monthly_summary',
            'smtp_enabled'
        ];

        foreach ($booleanFields as $field) {
            $validated[$field] = isset($validated[$field]) && $validated[$field] == '1';
        }

        // Save all settings
        foreach ($validated as $key => $value) {
            $type = 'string';

            if (in_array($key, $booleanFields)) {
                $type = 'boolean';
            } elseif (in_array($key, ['smtp_port', 'domain_expiry_days_before', 'subscription_renewal_days', 'monthly_summary_day'])) {
                $type = 'integer';
            }

            // Encrypt password before storing
            if ($key === 'smtp_password' && !empty($value)) {
                $value = encrypt($value);
            }

            ApplicationSetting::set($key, $value, $type);
        }

        return redirect()->route('settings.notifications')
            ->with('success', 'Notification settings updated successfully!');
    }

    /**
     * Send test email
     */
    public function sendTestEmail(Request $request)
    {
        $request->validate([
            'test_email' => 'required|email',
        ]);

        try {
            \Mail::raw('This is a test email from your ERP system. If you received this, your email configuration is working correctly!', function ($message) use ($request) {
                $message->to($request->test_email)
                    ->subject('Test Email from ERP System');
            });

            return response()->json([
                'success' => true,
                'message' => 'Test email sent successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send test email: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the yearly objectives settings page
     */
    public function yearlyObjectives()
    {
        $budgetThresholds = auth()->user()->getBudgetThresholds();

        return view('settings.yearly-objectives', compact('budgetThresholds'));
    }

    /**
     * Update yearly objectives (budget thresholds)
     */
    public function updateYearlyObjectives(Request $request)
    {
        $validated = $request->validate([
            'expense_budget_ron' => 'nullable|numeric|min:0',
            'expense_budget_eur' => 'nullable|numeric|min:0',
            'revenue_target_ron' => 'nullable|numeric|min:0',
            'revenue_target_eur' => 'nullable|numeric|min:0',
            'profit_margin_min' => 'nullable|numeric|min:0|max:100',
        ]);

        auth()->user()->saveBudgetThresholds($validated);

        return redirect()->route('settings.yearly-objectives')
            ->with('success', __('Yearly objectives saved successfully!'));
    }
}
