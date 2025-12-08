<?php

namespace App\Http\Controllers;

use App\Models\ApplicationSetting;
use App\Services\Settings\ApplicationSettingsService;
use App\Services\NomenclatureService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    protected ApplicationSettingsService $settingsService;
    protected NomenclatureService $nomenclatureService;

    /**
     * Ensure only admins and superadmins can access settings
     */
    public function __construct(
        ApplicationSettingsService $settingsService,
        NomenclatureService $nomenclatureService
    ) {
        $this->settingsService = $settingsService;
        $this->nomenclatureService = $nomenclatureService;
        $this->middleware('role:admin,superadmin');
    }

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
        $appSettings = $this->settingsService->getApplicationSettings();
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
        $counts = $this->nomenclatureService->getCounts();

        return view('settings.nomenclature.index', compact('counts'));
    }

    /**
     * Display client statuses settings
     */
    public function clientStatuses()
    {
        $data = $this->nomenclatureService->getClientStatuses();
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
        $data = $this->nomenclatureService->getDomainStatuses();
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
        $data = $this->nomenclatureService->getSubscriptionStatuses();
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
        $data = $this->nomenclatureService->getAccessPlatforms();
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
        $data = $this->nomenclatureService->getExpenseCategories();
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
        $data = $this->nomenclatureService->getPaymentMethods();
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
        $data = $this->nomenclatureService->getBillingCycles();
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
        $data = $this->nomenclatureService->getDomainRegistrars();
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
        $data = $this->nomenclatureService->getCurrencies();
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
        $data = $this->nomenclatureService->getQuickActions();
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
        $notificationSettings = $this->settingsService->getNotificationSettings();
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

        $this->settingsService->updateSettings(
            $validated,
            $this->settingsService->getNotificationBooleanFields(),
            $this->settingsService->getNotificationIntegerFields(),
            $this->settingsService->getNotificationEncryptedFields()
        );

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
            // Check if custom SMTP is enabled and configure it
            $smtpEnabled = ApplicationSetting::get('smtp_enabled', false);

            if ($smtpEnabled) {
                $smtpHost = ApplicationSetting::get('smtp_host');
                $smtpPort = ApplicationSetting::get('smtp_port', 587);
                $smtpUsername = ApplicationSetting::get('smtp_username');
                $smtpPassword = ApplicationSetting::get('smtp_password');
                $smtpEncryption = ApplicationSetting::get('smtp_encryption', 'tls');
                $fromEmail = ApplicationSetting::get('smtp_from_email');
                $fromName = ApplicationSetting::get('smtp_from_name', config('app.name'));

                if (!$smtpHost || !$smtpUsername) {
                    return response()->json([
                        'success' => false,
                        'message' => __('SMTP settings are incomplete. Please configure SMTP host and username.')
                    ], 400);
                }

                // Decrypt password if encrypted
                try {
                    $smtpPassword = decrypt($smtpPassword);
                } catch (\Exception $e) {
                    // Password might not be encrypted
                }

                // Configure SMTP on the fly
                config([
                    'mail.default' => 'smtp',
                    'mail.mailers.smtp.host' => $smtpHost,
                    'mail.mailers.smtp.port' => (int) $smtpPort,
                    'mail.mailers.smtp.username' => $smtpUsername,
                    'mail.mailers.smtp.password' => $smtpPassword,
                    'mail.mailers.smtp.encryption' => $smtpEncryption === 'none' ? null : $smtpEncryption,
                    'mail.from.address' => $fromEmail ?: $smtpUsername,
                    'mail.from.name' => $fromName,
                ]);
            } else {
                // Check if default mail config is usable
                $defaultMailer = config('mail.default');
                if ($defaultMailer === 'log') {
                    return response()->json([
                        'success' => false,
                        'message' => __('Email is configured to log only. Enable Custom SMTP or configure MAIL_MAILER in .env to send real emails.')
                    ], 400);
                }
            }

            \Mail::raw(__('This is a test email from your ERP system. If you received this, your email configuration is working correctly!'), function ($message) use ($request) {
                $message->to($request->test_email)
                    ->subject(__('Test Email from ERP System'));
            });

            return response()->json([
                'success' => true,
                'message' => __('Test email sent successfully to :email!', ['email' => $request->test_email])
            ]);
        } catch (\Exception $e) {
            \Log::error('Test email failed: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => __('Failed to send test email: :error', ['error' => $e->getMessage()])
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
