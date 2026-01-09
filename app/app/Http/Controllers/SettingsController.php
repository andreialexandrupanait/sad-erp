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
     * Nomenclature category configurations
     */
    private function getNomenclatureConfig(): array
    {
        return [
            'client_statuses' => [
                'title' => 'Status clienti',
                'description' => 'Gestioneaza statusurile clientilor folosite in aplicatie',
                'getter' => 'getClientStatuses',
                'hasColors' => true,
            ],
            'domain_statuses' => [
                'title' => 'Status domenii',
                'description' => 'Gestioneaza statusurile domeniilor',
                'getter' => 'getDomainStatuses',
                'hasColors' => true,
            ],
            'subscription_statuses' => [
                'title' => 'Status abonamente',
                'description' => 'Gestioneaza statusurile abonamentelor',
                'getter' => 'getSubscriptionStatuses',
                'hasColors' => true,
            ],
            'access_platforms' => [
                'title' => 'Categorii platforme',
                'description' => 'Gestioneaza tipurile de platforme de acces',
                'getter' => 'getAccessPlatforms',
                'hasColors' => true,
            ],
            'expense_categories' => [
                'title' => 'Categorii cheltuieli',
                'description' => 'Gestioneaza categoriile de cheltuieli',
                'getter' => 'getExpenseCategories',
                'hasColors' => true,
                'isHierarchical' => true,
            ],
            'payment_methods' => [
                'title' => 'Metode de plata',
                'description' => 'Gestioneaza metodele de plata disponibile',
                'getter' => 'getPaymentMethods',
                'hasColors' => true,
            ],
            'billing_cycles' => [
                'title' => 'Cicluri de facturare',
                'description' => 'Gestioneaza ciclurile de facturare pentru abonamente',
                'getter' => 'getBillingCycles',
                'hasColors' => true,
            ],
            'domain_registrars' => [
                'title' => 'Registratori de domenii',
                'description' => 'Gestioneaza registratorii de domenii',
                'getter' => 'getDomainRegistrars',
                'hasColors' => true,
            ],
            'currencies' => [
                'title' => 'Valute',
                'description' => 'Gestioneaza valutele disponibile in aplicatie',
                'getter' => 'getCurrencies',
                'hasColors' => true,
            ],
            'dashboard_quick_actions' => [
                'title' => 'Quick Actions Dashboard',
                'description' => 'Gestioneaza butoanele de actiune rapida de pe dashboard',
                'getter' => 'getQuickActions',
                'hasColors' => true,
            ],
        ];
    }

    /**
     * Generic nomenclature view handler
     */
    private function showNomenclature(string $category): \Illuminate\View\View
    {
        $config = $this->getNomenclatureConfig()[$category] ?? null;

        if (!$config) {
            abort(404);
        }

        $data = $this->nomenclatureService->{$config['getter']}();

        return view('settings.nomenclature', [
            'category' => $category,
            'title' => $config['title'],
            'description' => $config['description'],
            'data' => $data,
            'hasColors' => $config['hasColors'] ?? false,
            'isHierarchical' => $config['isHierarchical'] ?? false,
        ]);
    }

    // Nomenclature route handlers - delegate to generic handler
    public function clientStatuses() { return $this->showNomenclature('client_statuses'); }
    public function domainStatuses() { return $this->showNomenclature('domain_statuses'); }
    public function subscriptionStatuses() { return $this->showNomenclature('subscription_statuses'); }
    public function accessPlatforms() { return $this->showNomenclature('access_platforms'); }
    public function expenseCategories() { return $this->showNomenclature('expense_categories'); }
    public function paymentMethods() { return $this->showNomenclature('payment_methods'); }
    public function billingCycles() { return $this->showNomenclature('billing_cycles'); }
    public function domainRegistrars() { return $this->showNomenclature('domain_registrars'); }
    public function currencies() { return $this->showNomenclature('currencies'); }
    public function quickActions() { return $this->showNomenclature('dashboard_quick_actions'); }

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
     * Business Information Settings
     */
    public function businessInfo()
    {
        $organization = auth()->user()->organization;
        return view('settings.business-info', compact('organization'));
    }

    /**
     * Update Business Information
     */
    public function updateBusinessInfo(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'tax_id' => 'required|string|max:50',
            'trade_registry' => 'required|string|max:50',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'county' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'logo' => 'nullable|image|max:2048',
            'bank_accounts' => 'nullable|array',
            'bank_accounts.*.iban' => 'nullable|string|max:50',
            'bank_accounts.*.bank' => 'nullable|string|max:100',
            'bank_accounts.*.currency' => 'nullable|string|max:3',
            'bank_accounts.*.description' => 'nullable|string|max:100',
        ]);

        $organization = auth()->user()->organization;

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($organization->logo && \Storage::disk('public')->exists($organization->logo)) {
                \Storage::disk('public')->delete($organization->logo);
            }
            $logoPath = $request->file('logo')->store('logos', 'public');
            $organization->logo = $logoPath;
        }

        // Handle logo removal
        if ($request->has('remove_logo') && $organization->logo) {
            if (\Storage::disk('public')->exists($organization->logo)) {
                \Storage::disk('public')->delete($organization->logo);
            }
            $organization->logo = null;
        }

        // Update basic fields
        $organization->name = $request->name;
        $organization->tax_id = $request->tax_id;
        $organization->address = $request->address;
        $organization->phone = $request->phone;
        $organization->email = $request->email;

        // Update settings JSON with all new fields
        $settings = $organization->settings ?? [];
        $settings['trade_registry'] = $request->trade_registry;
        $settings['vat_id'] = $request->vat_id;
        $settings['share_capital'] = $request->share_capital;
        $settings['representative'] = $request->representative;
        $settings['city'] = $request->city;
        $settings['county'] = $request->county;
        $settings['country'] = $request->country;
        $settings['vat_payer'] = (bool) $request->vat_payer;
        $settings['fax'] = $request->fax;
        $settings['website'] = $request->website;
        $settings['additional_info'] = $request->additional_info;

        // Process bank accounts - filter out empty ones
        $bankAccounts = collect($request->bank_accounts ?? [])
            ->filter(fn($account) => !empty($account['iban']) || !empty($account['bank']))
            ->values()
            ->toArray();
        $settings['bank_accounts'] = $bankAccounts;

        // Document prefixes
        if ($request->has('offer_prefix')) {
            $settings['offer_prefix'] = $request->offer_prefix ?: 'OFR';
        }
        if ($request->has('contract_prefix')) {
            $settings['contract_prefix'] = $request->contract_prefix ?: 'CTR';
        }

        $organization->settings = $settings;

        $organization->save();

        return redirect()->route('settings.business-info')->with('success', __('Informațiile companiei au fost actualizate.'));
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
     * Update Offer Defaults Settings (API for offer builder sidebar)
     */
    public function updateOfferDefaults(Request $request)
    {
        $organization = auth()->user()->organization;
        $settings = $organization->settings ?? [];

        // Process specifications - filter out empty ones (only if provided)
        $specifications = $settings['offer_defaults']['specifications'] ?? [];
        if ($request->has('specifications')) {
            $specifications = collect($request->specifications ?? [])
                ->filter(fn($spec) => !empty($spec['content']) || !empty(array_filter($spec['items'] ?? [])))
                ->map(function ($spec) {
                    return [
                        'title' => $spec['title'] ?? '',
                        'type' => $spec['type'] ?? 'list',
                        'content' => $spec['content'] ?? '',
                        'items' => array_values(array_filter($spec['items'] ?? [])),
                    ];
                })
                ->values()
                ->toArray();
        }

        // Process default services - filter out empty ones (only if provided)
        $defaultServices = $settings['offer_defaults']['default_services'] ?? [];
        if ($request->has('default_services')) {
            $defaultServices = collect($request->default_services ?? [])
                ->filter(fn($svc) => !empty($svc['title']))
                ->map(function ($svc) {
                    return [
                        'title' => $svc['title'] ?? '',
                        'description' => $svc['description'] ?? '',
                        'unit_price' => (float) ($svc['unit_price'] ?? 0),
                        'unit' => $svc['unit'] ?? 'proiect',
                        'service_id' => $svc['service_id'] ?? null,
                        'selected' => (bool) ($svc['selected'] ?? true),
                        'type' => $svc['type'] ?? 'card', // 'custom' for checkbox list, 'card' for extra service cards
                    ];
                })
                ->values()
                ->toArray();
        }

        $settings['offer_defaults'] = [
            'header_intro_title' => $request->header_intro_title ?? $settings['offer_defaults']['header_intro_title'] ?? '',
            'header_intro_text' => $request->header_intro_text ?? $settings['offer_defaults']['header_intro_text'] ?? '',
            'acceptance_paragraph' => $request->acceptance_paragraph ?? $settings['offer_defaults']['acceptance_paragraph'] ?? '',
            'accept_button_text' => $request->accept_button_text ?: __('Accept Offer'),
            'decline_button_text' => $request->decline_button_text ?: __('Decline'),
            'brands_heading' => $request->brands_heading ?? $settings['offer_defaults']['brands_heading'] ?? '',
            'brands_image' => $request->brands_image ?? $settings['offer_defaults']['brands_image'] ?? '',
            'specifications' => $specifications,
            'default_services' => $defaultServices,
            'validity_days' => (int) ($request->validity_days ?? 30),
            'currency' => $request->currency ?? 'RON',
            'show_vat' => $request->boolean('show_vat'),
            'vat_percent' => (int) ($request->vat_percent ?? 19),
        ];

        $organization->settings = $settings;
        $organization->save();

        // Return JSON for AJAX requests
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Offer defaults saved successfully.'),
            ]);
        }

        return redirect()->route('settings.offer-defaults')
            ->with('success', __('Offer defaults saved successfully.'));
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
            'push_notifications_enabled' => 'nullable|boolean',
            'push_notify_offer_accepted' => 'nullable|boolean',
            'push_notify_offer_rejected' => 'nullable|boolean',
            'push_notify_new_client' => 'nullable|boolean',
            'push_notify_payment_received' => 'nullable|boolean',
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
