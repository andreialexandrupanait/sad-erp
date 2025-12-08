<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\ApplicationSetting;
use App\Services\Notification\Channels\EmailChannel;
use Illuminate\Http\Request;

class EmailNotificationController extends Controller
{
    /**
     * Show Email notification settings page
     */
    public function index()
    {
        $emailSettings = [
            'email_notifications_enabled' => ApplicationSetting::get('email_notifications_enabled', false),
            'email_notifications_admin' => ApplicationSetting::get('email_notifications_admin', ''),

            // Notification types
            'email_notify_domain_expiry' => ApplicationSetting::get('email_notify_domain_expiry', true),
            'email_notify_subscription_renewal' => ApplicationSetting::get('email_notify_subscription_renewal', true),
            'email_notify_new_revenue' => ApplicationSetting::get('email_notify_new_revenue', false),
            'email_notify_client_status' => ApplicationSetting::get('email_notify_client_status', false),
            'email_notify_system_errors' => ApplicationSetting::get('email_notify_system_errors', true),
        ];

        // Get current mail configuration for display
        $mailConfig = [
            'driver' => config('mail.default'),
            'host' => config('mail.mailers.smtp.host'),
            'from_address' => config('mail.from.address'),
            'from_name' => config('mail.from.name'),
        ];

        return view('settings.integrations.email', compact('emailSettings', 'mailConfig'));
    }

    /**
     * Update Email notification settings
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'email_notifications_enabled' => 'nullable|boolean',
            'email_notifications_admin' => 'nullable|string|max:500',
            'email_notify_domain_expiry' => 'nullable|boolean',
            'email_notify_subscription_renewal' => 'nullable|boolean',
            'email_notify_new_revenue' => 'nullable|boolean',
            'email_notify_client_status' => 'nullable|boolean',
            'email_notify_system_errors' => 'nullable|boolean',
        ]);

        // Validate email addresses if provided
        if (!empty($validated['email_notifications_admin'])) {
            $emails = array_map('trim', explode(',', $validated['email_notifications_admin']));
            foreach ($emails as $email) {
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    return back()->withErrors(['email_notifications_admin' => __('Invalid email address: :email', ['email' => $email])]);
                }
            }
        }

        // Convert checkbox values (null means unchecked = false)
        $booleanFields = [
            'email_notifications_enabled',
            'email_notify_domain_expiry',
            'email_notify_subscription_renewal',
            'email_notify_new_revenue',
            'email_notify_client_status',
            'email_notify_system_errors',
        ];

        foreach ($booleanFields as $field) {
            $validated[$field] = isset($validated[$field]) && $validated[$field] == '1';
        }

        // Save all settings
        foreach ($validated as $key => $value) {
            $type = in_array($key, $booleanFields) ? 'boolean' : 'string';
            ApplicationSetting::set($key, $value, $type);
        }

        return redirect()->route('settings.email.index')
            ->with('success', __('Email notification settings updated successfully!'));
    }

    /**
     * Test Email notification
     */
    public function test(Request $request)
    {
        $testEmail = $request->input('test_email');

        // Validate test email if provided
        if ($testEmail && !filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                'success' => false,
                'message' => __('Invalid email address.')
            ], 400);
        }

        try {
            $emailChannel = app(EmailChannel::class);

            if (!$emailChannel->isConfigured()) {
                return response()->json([
                    'success' => false,
                    'message' => __('Email is not configured. Please check your mail settings in .env file.')
                ], 400);
            }

            // Use test email or fall back to admin email
            $recipient = $testEmail ?: $emailChannel->getAdminEmail();

            if (empty($recipient)) {
                return response()->json([
                    'success' => false,
                    'message' => __('No recipient email specified. Please enter an email address or configure admin email.')
                ], 400);
            }

            $result = $emailChannel->sendTest($recipient);

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => __('Test email sent successfully to :email!', ['email' => $recipient])
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => __('Failed to send test email.')
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('Failed to send test email') . ': ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Disable Email notifications
     */
    public function disconnect()
    {
        ApplicationSetting::set('email_notifications_enabled', false, 'boolean');

        return redirect()->route('settings.email.index')
            ->with('success', __('Email notifications disabled.'));
    }
}
