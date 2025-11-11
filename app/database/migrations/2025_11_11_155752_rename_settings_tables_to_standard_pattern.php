<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Rename settings tables to follow settings_[module] naming convention:
     * - application_settings → settings_application
     * - client_settings → settings_client
     * - financial_settings → settings_financial
     * - system_settings → settings_system
     */
    public function up(): void
    {
        // Rename application_settings to settings_application
        Schema::rename('application_settings', 'settings_application');

        // Rename client_settings to settings_client
        Schema::rename('client_settings', 'settings_client');

        // Rename financial_settings to settings_financial
        Schema::rename('financial_settings', 'settings_financial');

        // Rename system_settings to settings_system
        Schema::rename('system_settings', 'settings_system');
    }

    /**
     * Reverse the migrations.
     *
     * Rename tables back to original names
     */
    public function down(): void
    {
        // Revert settings_application to application_settings
        Schema::rename('settings_application', 'application_settings');

        // Revert settings_client to client_settings
        Schema::rename('settings_client', 'client_settings');

        // Revert settings_financial to financial_settings
        Schema::rename('settings_financial', 'financial_settings');

        // Revert settings_system to system_settings
        Schema::rename('settings_system', 'system_settings');
    }
};
