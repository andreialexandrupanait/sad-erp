<?php

namespace Database\Factories;

use App\Models\SettingOption;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SettingOption>
 */
class SettingOptionFactory extends Factory
{
    protected $model = SettingOption::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $label = fake()->words(2, true);

        return [
            'organization_id' => Organization::factory(),
            'parent_id' => null,
            'category' => 'client_statuses',
            'label' => ucwords($label),
            'value' => Str::slug($label),
            'color_class' => null,
            'sort_order' => 1,
            'is_active' => true,
            'is_default' => false,
        ];
    }
}
