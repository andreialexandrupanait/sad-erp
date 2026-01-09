<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\ApplicationSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class R2StorageController extends Controller
{
    /**
     * Show R2 storage settings page
     */
    public function index()
    {
        $r2Settings = [
            'r2_enabled' => ApplicationSetting::get('r2_enabled', false),
            'r2_access_key_id' => ApplicationSetting::get('r2_access_key_id', ''),
            'r2_secret_access_key' => ApplicationSetting::get('r2_secret_access_key', ''),
            'r2_bucket' => ApplicationSetting::get('r2_bucket', ''),
            'r2_endpoint' => ApplicationSetting::get('r2_endpoint', ''),
            'r2_region' => ApplicationSetting::get('r2_region', 'auto'),
            'r2_use_for_financial' => ApplicationSetting::get('r2_use_for_financial', false),
            'r2_use_for_contracts' => ApplicationSetting::get('r2_use_for_contracts', false),
        ];

        return view('settings.integrations.r2', compact('r2Settings'));
    }

    /**
     * Update R2 settings
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'r2_enabled' => 'nullable|boolean',
            'r2_access_key_id' => 'nullable|required_if:r2_enabled,1|string|max:255',
            'r2_secret_access_key' => 'nullable|string|max:255',
            'r2_bucket' => 'nullable|required_if:r2_enabled,1|string|max:255',
            'r2_endpoint' => 'nullable|required_if:r2_enabled,1|url|max:500',
            'r2_region' => 'nullable|string|max:50',
            'r2_use_for_financial' => 'nullable|boolean',
            'r2_use_for_contracts' => 'nullable|boolean',
        ]);

        // Convert checkbox values
        $booleanFields = ['r2_enabled', 'r2_use_for_financial', 'r2_use_for_contracts'];
        foreach ($booleanFields as $field) {
            $validated[$field] = isset($validated[$field]) && $validated[$field] == '1';
        }

        // If secret key is empty and we already have one, keep the existing one
        if (empty($validated['r2_secret_access_key'])) {
            unset($validated['r2_secret_access_key']);
        }

        // Save all settings
        foreach ($validated as $key => $value) {
            $type = in_array($key, $booleanFields) ? 'boolean' : 'string';
            ApplicationSetting::set($key, $value, $type);
        }

        // Clear config cache so changes take effect
        \Artisan::call('config:clear');

        return redirect()->route('settings.r2.index')
            ->with('success', __('R2 storage settings updated successfully!'));
    }

    /**
     * Test R2 connection
     */
    public function test()
    {
        try {
            $accessKey = ApplicationSetting::get('r2_access_key_id');
            $secretKey = ApplicationSetting::get('r2_secret_access_key');
            $bucket = ApplicationSetting::get('r2_bucket');
            $endpoint = ApplicationSetting::get('r2_endpoint');
            $region = ApplicationSetting::get('r2_region', 'auto');

            if (empty($accessKey) || empty($secretKey) || empty($bucket) || empty($endpoint)) {
                return response()->json([
                    'success' => false,
                    'message' => __('R2 is not configured. Please save your settings first.')
                ], 400);
            }

            // Create a temporary disk with current settings
            $disk = Storage::build([
                'driver' => 's3',
                'key' => $accessKey,
                'secret' => $secretKey,
                'region' => $region,
                'bucket' => $bucket,
                'endpoint' => $endpoint,
                'use_path_style_endpoint' => false,
            ]);

            // Test write
            $testFile = '_r2_connection_test_' . time() . '.txt';
            $disk->put($testFile, 'Connection test from ERP at ' . now()->toISOString());

            // Test read
            $content = $disk->get($testFile);

            // Test delete
            $disk->delete($testFile);

            return response()->json([
                'success' => true,
                'message' => __('R2 connection successful! Write, read, and delete operations working correctly.')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('R2 connection failed') . ': ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Disconnect R2 integration
     */
    public function disconnect()
    {
        ApplicationSetting::set('r2_enabled', false, 'boolean');
        ApplicationSetting::set('r2_use_for_financial', false, 'boolean');
        ApplicationSetting::set('r2_use_for_contracts', false, 'boolean');

        // Clear config cache
        \Artisan::call('config:clear');

        return redirect()->route('settings.r2.index')
            ->with('success', __('R2 storage integration disconnected.'));
    }
}
