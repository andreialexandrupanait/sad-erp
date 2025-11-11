<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, boolean, json, file
            $table->timestamps();
        });

        // Insert default settings
        DB::table('application_settings')->insert([
            ['key' => 'app_name', 'value' => 'ERP System', 'type' => 'string', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'app_logo', 'value' => null, 'type' => 'file', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'app_favicon', 'value' => null, 'type' => 'file', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'theme_mode', 'value' => 'light', 'type' => 'string', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'primary_color', 'value' => '#3b82f6', 'type' => 'string', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'language', 'value' => 'ro', 'type' => 'string', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'timezone', 'value' => 'Europe/Bucharest', 'type' => 'string', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'date_format', 'value' => 'd/m/Y', 'type' => 'string', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('application_settings');
    }
};
