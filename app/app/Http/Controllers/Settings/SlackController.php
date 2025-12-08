<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\ApplicationSetting;
use App\Services\Notification\Channels\SlackChannel;
use Illuminate\Http\Request;

class SlackController extends Controller
{
    /**
     * Show Slack integration settings page
     */
    public function index()
    {
        $slackSettings = [
            'slack_enabled' => ApplicationSetting::get('slack_enabled', false),
            'slack_webhook_url' => ApplicationSetting::get('slack_webhook_url', ''),
            'slack_channel' => ApplicationSetting::get('slack_channel', ''),

            // Notification types
            'slack_notify_domain_expiry' => ApplicationSetting::get('slack_notify_domain_expiry', true),
            'slack_notify_subscription_renewal' => ApplicationSetting::get('slack_notify_subscription_renewal', true),
            'slack_notify_new_revenue' => ApplicationSetting::get('slack_notify_new_revenue', false),
            'slack_notify_client_status' => ApplicationSetting::get('slack_notify_client_status', false),
            'slack_notify_system_errors' => ApplicationSetting::get('slack_notify_system_errors', true),
        ];

        return view('settings.integrations.slack', compact('slackSettings'));
    }

    /**
     * Update Slack settings
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'slack_enabled' => 'nullable|boolean',
            'slack_webhook_url' => 'nullable|required_if:slack_enabled,1|url|max:500',
            'slack_channel' => 'nullable|string|max:100',
            'slack_notify_domain_expiry' => 'nullable|boolean',
            'slack_notify_subscription_renewal' => 'nullable|boolean',
            'slack_notify_new_revenue' => 'nullable|boolean',
            'slack_notify_client_status' => 'nullable|boolean',
            'slack_notify_system_errors' => 'nullable|boolean',
        ]);

        // Convert checkbox values (null means unchecked = false)
        $booleanFields = [
            'slack_enabled',
            'slack_notify_domain_expiry',
            'slack_notify_subscription_renewal',
            'slack_notify_new_revenue',
            'slack_notify_client_status',
            'slack_notify_system_errors',
        ];

        foreach ($booleanFields as $field) {
            $validated[$field] = isset($validated[$field]) && $validated[$field] == '1';
        }

        // Save all settings
        foreach ($validated as $key => $value) {
            $type = in_array($key, $booleanFields) ? 'boolean' : 'string';
            ApplicationSetting::set($key, $value, $type);
        }

        return redirect()->route('settings.slack.index')
            ->with('success', __('Slack settings updated successfully!'));
    }

    /**
     * Test Slack connection
     */
    public function test()
    {
        try {
            $slackChannel = app(SlackChannel::class);

            if (!$slackChannel->isConfigured()) {
                return response()->json([
                    'success' => false,
                    'message' => __('Slack is not configured. Please save your webhook URL first.')
                ], 400);
            }

            $result = $slackChannel->sendTest();

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => __('Test notification sent to Slack successfully!')
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => __('Failed to send test notification to Slack.')
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('Failed to send test notification') . ': ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Disconnect Slack integration
     */
    public function disconnect()
    {
        ApplicationSetting::set('slack_enabled', false, 'boolean');
        ApplicationSetting::set('slack_webhook_url', '', 'string');

        return redirect()->route('settings.slack.index')
            ->with('success', __('Slack integration disconnected.'));
    }
}
