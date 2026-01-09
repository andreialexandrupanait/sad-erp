<?php

namespace App\Providers;

use App\Models\ApplicationSetting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class StorageConfigServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (!$this->databaseAvailable()) {
            return;
        }

        try {
            $r2Enabled = ApplicationSetting::get('r2_enabled', false);
            
            if (!$r2Enabled) {
                return;
            }

            $accessKey = ApplicationSetting::get('r2_access_key_id');
            $secretKey = ApplicationSetting::get('r2_secret_access_key');
            $bucket = ApplicationSetting::get('r2_bucket');
            $endpoint = ApplicationSetting::get('r2_endpoint');
            $region = ApplicationSetting::get('r2_region', 'auto');

            if (empty($accessKey) || empty($secretKey) || empty($bucket) || empty($endpoint)) {
                return;
            }

            // Configure the R2 disk
            Config::set('filesystems.disks.r2', [
                'driver' => 's3',
                'key' => $accessKey,
                'secret' => $secretKey,
                'region' => $region,
                'bucket' => $bucket,
                'endpoint' => $endpoint,
                'use_path_style_endpoint' => false,
                'visibility' => 'private',
                'throw' => false,
                'report' => false,
            ]);

            // Update financial disk if R2 is enabled for financial files
            // Note: Files are stored in R2 under financial/ prefix
            $useForFinancial = ApplicationSetting::get('r2_use_for_financial', false);
            if ($useForFinancial) {
                Config::set('filesystems.disks.financial', [
                    'driver' => 's3',
                    'key' => $accessKey,
                    'secret' => $secretKey,
                    'region' => $region,
                    'bucket' => $bucket,
                    'endpoint' => $endpoint,
                    'use_path_style_endpoint' => false,
                    'visibility' => 'private',
                    'throw' => false,
                    'report' => false,
                    'root' => 'financial',  // Files are stored under financial/ prefix
                ]);
            }

            // Update contracts disk if R2 is enabled for contracts
            $useForContracts = ApplicationSetting::get('r2_use_for_contracts', false);
            if ($useForContracts) {
                Config::set('filesystems.contracts_disk', 'r2');
                
                // Also configure the new documents disk to use R2
                Config::set('filesystems.disks.documents', [
                    'driver' => 's3',
                    'key' => $accessKey,
                    'secret' => $secretKey,
                    'region' => $region,
                    'bucket' => $bucket,
                    'endpoint' => $endpoint,
                    'use_path_style_endpoint' => false,
                    'visibility' => 'private',
                    'throw' => false,
                    'report' => false,
                ]);
                Config::set('filesystems.documents_disk', 'documents');
            }

        } catch (\Exception $e) {
            \Log::debug('StorageConfigServiceProvider: Could not configure R2', [
                'error' => $e->getMessage()
            ]);
        }
    }

    protected function databaseAvailable(): bool
    {
        try {
            \DB::connection()->getPdo();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
