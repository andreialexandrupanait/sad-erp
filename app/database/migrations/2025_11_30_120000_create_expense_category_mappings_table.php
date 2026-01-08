<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_category_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->string('pattern'); // Pattern to match (e.g., "SIMPLENET", "MOL")
            $table->string('category'); // Category value from settings_options
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['organization_id', 'pattern']);
            $table->index(['organization_id', 'is_active']);
        });

        // Seed default mappings for organization 1
        $this->seedDefaultMappings();
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_category_mappings');
    }

    private function seedDefaultMappings(): void
    {
        // Skip seeding during tests (organization_id=1 may not exist)
        if (app()->environment('testing')) {
            return;
        }

        // Check if organization_id=1 exists before seeding
        $organizationExists = \DB::table('organizations')->where('id', 1)->exists();
        if (!$organizationExists) {
            return;
        }

        $mappings = [
            ['pattern' => 'Salarii', 'category' => 'salarii'],
            ['pattern' => 'Salariu', 'category' => 'salarii'],
            ['pattern' => 'Comision plata OP', 'category' => 'comisioane-bancare'],
            ['pattern' => 'Pachet IZI', 'category' => 'comisioane-bancare'],
            ['pattern' => 'SIMPLENET', 'category' => 'hosting'],
            ['pattern' => 'hosting', 'category' => 'hosting'],
            ['pattern' => 'MOL', 'category' => 'combustibil'],
            ['pattern' => 'OMV', 'category' => 'combustibil'],
            ['pattern' => 'PETROM', 'category' => 'combustibil'],
            ['pattern' => 'benzin', 'category' => 'combustibil'],
            ['pattern' => 'TREZORERIE', 'category' => 'taxe'],
            ['pattern' => 'ANAF', 'category' => 'taxe'],
            ['pattern' => 'Casco', 'category' => 'asigurari'],
            ['pattern' => 'RCA', 'category' => 'asigurari'],
            ['pattern' => 'asigur', 'category' => 'asigurari'],
            ['pattern' => 'Leasing', 'category' => 'leasing'],
            ['pattern' => 'OPENAI', 'category' => 'subscriptii'],
            ['pattern' => 'ChatGPT', 'category' => 'subscriptii'],
            ['pattern' => 'subscri', 'category' => 'subscriptii'],
            ['pattern' => 'Dividende', 'category' => 'dividende'],
            ['pattern' => 'APPROD', 'category' => 'software'],
            ['pattern' => 'SOFTWARE', 'category' => 'software'],
        ];

        $now = now();
        $organizationId = 1; // Default organization

        foreach ($mappings as $mapping) {
            \DB::table('expense_category_mappings')->insert([
                'organization_id' => $organizationId,
                'pattern' => $mapping['pattern'],
                'category' => $mapping['category'],
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
};
