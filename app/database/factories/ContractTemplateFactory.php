<?php

namespace Database\Factories;

use App\Models\ContractTemplate;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ContractTemplate>
 */
class ContractTemplateFactory extends Factory
{
    protected $model = ContractTemplate::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'name' => fake()->words(3, true) . ' Template',
            'description' => fake()->sentence(),
            'content' => '<h1>Contract Template</h1><p>{{client_name}}</p><p>{{contract_total}} {{currency}}</p>',
            'is_default' => false,
            'is_active' => true,
        ];
    }

    public function default(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_default' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function withContent(string $content): static
    {
        return $this->state(fn(array $attributes) => [
            'content' => $content,
        ]);
    }
}
