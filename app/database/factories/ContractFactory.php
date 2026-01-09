<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Contract;
use App\Models\Offer;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Contract>
 */
class ContractFactory extends Factory
{
    protected $model = Contract::class;

    public function definition(): array
    {
        $year = date('Y');

        return [
            'organization_id' => Organization::factory(),
            'client_id' => Client::factory(),
            'offer_id' => Offer::factory(),
            'contract_number' => fn() => sprintf('%02d', fake()->unique()->numberBetween(1, 99)),
            'title' => fake()->sentence(4),
            'content' => fake()->paragraphs(3, true),
            'status' => 'draft',
            'total_value' => fake()->randomFloat(2, 100, 10000),
            'currency' => fake()->randomElement(['EUR', 'RON', 'USD']),
            'start_date' => now(),
            'end_date' => now()->addYear(),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'draft',
        ]);
    }

    public function active(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'active',
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'expired',
            'end_date' => now()->subDays(30),
        ]);
    }

    public function terminated(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'terminated',
        ]);
    }

    public function indefinite(): static
    {
        return $this->state(fn(array $attributes) => [
            'end_date' => null,
        ]);
    }

    public function expiringIn(int $days): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'active',
            'end_date' => now()->addDays($days),
        ]);
    }

    public function withTempClient(): static
    {
        return $this->state(fn(array $attributes) => [
            'client_id' => null,
            'temp_client_name' => fake()->name(),
            'temp_client_email' => fake()->email(),
            'temp_client_company' => fake()->company(),
        ]);
    }
}
