<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Skip seeding during tests (organization_id=1 may not exist)
        if (app()->environment('testing')) {
            return;
        }

        // Check if organization_id=1 exists before seeding
        $organizationExists = DB::table('organizations')->where('id', 1)->exists();
        if (!$organizationExists) {
            return;
        }

        $organizationId = 1;
        $now = now();
        $sortOrder = 1;

        // Define categories with their subcategories
        $categories = [
            'Abonamente' => [
                'Adobe Creative Cloud',
                'Elementor',
                'Envato',
                'Freepik',
                'Ionos',
                'Rank Math',
                'WP Jetor',
                'Zoho',
            ],
            'Echipamente' => [
                'Laptop/Calculator',
                'Imprimanta',
            ],
            'Flori PSI' => [],
            'Licente' => [],
            'Utilități' => [
                'Energie Electrica',
                'Gaz',
                'Apa',
                'Internet',
                'Telefon',
                'Gunoi',
            ],
            'Servicii' => [
                'Contabilitate',
                'Consultanta',
                'Servicii active',
                'Transport',
                'Curierat',
            ],
            'Auto' => [
                'Combustibil',
                'Service',
                'Spalatorie',
                'ITP',
                'Vinieta',
                'Rovinieta',
                'Parcare',
            ],
            'Dotari' => [
                'Diverse/Altii',
                'Diverse/Imprimanta',
                'Alte[x]',
                'Jysk',
                'Consumabile',
                'Papetarie',
            ],
            'Publicitate' => [
                'Google Ads',
                'Facebook/Instagram',
                'Reclame/Banner',
                'Marketing/Promovare',
            ],
            'Personal' => [
                'Salarii/Salariati',
                'Diurna',
            ],
            'Reprezentare' => [
                'Masa',
                'Cafea',
                'Catering',
                'Decoratiuni exteriori',
            ],
            'Biroul' => [
                'Spatiu/Depozitare',
                'Curatenie',
                'Chirie',
            ],
            'Taxe' => [
                'Taxe si impozite',
                'Taxe/Impozit auto',
                'Inmatriculari rigori autoturisme',
            ],
            'Inregistrari/Legale' => [
                'Inregistrari',
                'Inregistrari rigori',
                'Inregistrari rigori auto mobilie',
            ],
            'Imprumuturi/Leasing' => [
                'Imprumuturi',
                'Imprumuturi banca',
                'Leasing Auto',
            ],
        ];

        foreach ($categories as $parentLabel => $children) {
            // Create parent category
            $parentValue = $this->slugify($parentLabel);

            $parentId = DB::table('settings_options')->insertGetId([
                'organization_id' => $organizationId,
                'parent_id' => null,
                'category' => 'expense_categories',
                'label' => $parentLabel,
                'value' => $parentValue,
                'color_class' => null,
                'sort_order' => $sortOrder++,
                'is_active' => true,
                'is_default' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // Create children
            $childSortOrder = 1;
            foreach ($children as $childLabel) {
                $childValue = $parentValue . '-' . $this->slugify($childLabel);

                DB::table('settings_options')->insert([
                    'organization_id' => $organizationId,
                    'parent_id' => $parentId,
                    'category' => 'expense_categories',
                    'label' => $childLabel,
                    'value' => $childValue,
                    'color_class' => null,
                    'sort_order' => $childSortOrder++,
                    'is_active' => true,
                    'is_default' => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('settings_options')
            ->where('category', 'expense_categories')
            ->where('organization_id', 1)
            ->delete();
    }

    private function slugify(string $text): string
    {
        // Convert to lowercase and replace spaces/special chars with hyphens
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        $text = trim($text, '-');
        return $text;
    }
};
