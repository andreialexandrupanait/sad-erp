<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\ApplicationSetting;
use Illuminate\Http\Request;

class ClickUpController extends Controller
{
    /**
     * Show ClickUp integration settings page
     */
    public function index()
    {
        $clickupSettings = [
            'clickup_enabled' => ApplicationSetting::get('clickup_enabled', false),
            'clickup_api_token' => ApplicationSetting::get('clickup_api_token', ''),
            'clickup_team_id' => ApplicationSetting::get('clickup_team_id', ''),
        ];

        return view('settings.integrations.clickup', compact('clickupSettings'));
    }

    /**
     * Update ClickUp settings
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'clickup_enabled' => 'nullable|boolean',
            'clickup_api_token' => 'nullable|required_if:clickup_enabled,1|string|max:500',
            'clickup_team_id' => 'nullable|string|max:100',
        ]);

        // Convert checkbox values
        $validated['clickup_enabled'] = isset($validated['clickup_enabled']) && $validated['clickup_enabled'] == '1';

        // Save all settings
        ApplicationSetting::set('clickup_enabled', $validated['clickup_enabled'], 'boolean');

        if (isset($validated['clickup_api_token'])) {
            ApplicationSetting::set('clickup_api_token', $validated['clickup_api_token'], 'string');
        }

        if (isset($validated['clickup_team_id'])) {
            ApplicationSetting::set('clickup_team_id', $validated['clickup_team_id'], 'string');
        }

        return redirect()->route('settings.clickup.index')
            ->with('success', __('ClickUp settings updated successfully!'));
    }

    /**
     * Test ClickUp connection
     */
    public function test()
    {
        try {
            $apiToken = ApplicationSetting::get('clickup_api_token');

            if (empty($apiToken)) {
                return response()->json([
                    'success' => false,
                    'message' => __('ClickUp is not configured. Please save your API token first.')
                ], 400);
            }

            // Test API connection
            $client = new \GuzzleHttp\Client();
            $response = $client->get('https://api.clickup.com/api/v2/user', [
                'headers' => [
                    'Authorization' => $apiToken,
                    'Content-Type' => 'application/json',
                ],
                'timeout' => 10,
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if (isset($data['user'])) {
                return response()->json([
                    'success' => true,
                    'message' => __('Connected successfully as :name', ['name' => $data['user']['username'] ?? 'Unknown'])
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => __('Failed to verify ClickUp connection.')
            ], 500);

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            return response()->json([
                'success' => false,
                'message' => __('Invalid API token. Please check your credentials.')
            ], 401);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('Connection failed') . ': ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Disconnect ClickUp integration
     */
    public function disconnect()
    {
        ApplicationSetting::set('clickup_enabled', false, 'boolean');
        ApplicationSetting::set('clickup_api_token', '', 'string');
        ApplicationSetting::set('clickup_team_id', '', 'string');

        return redirect()->route('settings.clickup.index')
            ->with('success', __('ClickUp integration disconnected.'));
    }
}
