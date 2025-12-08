<?php

namespace Database\Factories;

use App\Models\FinancialExpense;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends Factory<FinancialExpense>
 */
class FinancialExpenseFactory extends Factory
{
    protected $model = FinancialExpense::class;

    public function definition(): array
    {
        $date = fake()->dateTimeBetween('-1 year', 'now');
        $carbonDate = Carbon::instance($date);

        return [
            'organization_id' => Organization::factory(),
            'user_id' => User::factory(),
            'document_name' => 'EXP-' . fake()->unique()->numerify('####'),
            'amount' => fake()->randomFloat(2, 50, 5000),
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

    public function inCurrency(string $currency): static
    {
        return $this->state(fn(array $attributes) => [
            'currency' => $currency,
        ]);
    }
}
