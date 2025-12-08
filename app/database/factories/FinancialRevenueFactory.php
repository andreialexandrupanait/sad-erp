<?php

namespace Database\Factories;

use App\Models\FinancialRevenue;
use App\Models\Organization;
use App\Models\User;
use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends Factory<FinancialRevenue>
 */
class FinancialRevenueFactory extends Factory
{
    protected $model = FinancialRevenue::class;

    public function definition(): array
    {
        $date = fake()->dateTimeBetween('-1 year', 'now');
        $carbonDate = Carbon::instance($date);

        return [
            'organization_id' => Organization::factory(),
            'user_id' => User::factory(),
            'document_name' => 'INV-' . fake()->unique()->numerify('####'),
            'amount' => fake()->randomFloat(2, 100, 10000),
            'currency' => 'RON',
            'occurred_at' => $carbonDate,
            'year' => $carbonDate->year,
            'month' => $carbonDate->month,
            'note' => fake()->optional()->sentence(),
        ];
    }

    public function forOrganization(Organization $organization): static
    {
        return $this->state(fn(array $attributes) => [
            'organization_id' => $organization->id,
        ]);
    }

    public function forUser(User $user): static
    {
        return $this->state(fn(array $attributes) => [
            'organization_id' => $user->organization_id,
            'user_id' => $user->id,
        ]);
    }

    public function forClient(Client $client): static
    {
        return $this->state(fn(array $attributes) => [
            'client_id' => $client->id,
        ]);
    }

    public function inCurrency(string $currency): static
    {
        return $this->state(fn(array $attributes) => [
            'currency' => $currency,
        ]);
    }
}
