<?php

namespace Database\Factories;

use App\Models\Organization;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subscription>
 */
class SubscriptionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Subscription::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('-1 year', 'now');
        $billingCycle = fake()->randomElement(['weekly', 'monthly', 'annual']);

        return [
            'organization_id' => Organization::factory(),
            'vendor_name' => fake()->company(),
            'price' => fake()->randomFloat(2, 50, 5000),
            'currency' => fake()->randomElement(['RON', 'EUR']),
            'billing_cycle' => $billingCycle,
            'custom_days' => null,
            'start_date' => $startDate,
            'next_renewal_date' => fake()->dateTimeBetween('now', '+1 year'),
            'status' => 'active',
            'notes' => fake()->optional()->paragraph(),
        ];
    }


    /**
     * Indicate that the subscription is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'next_renewal_date' => fake()->dateTimeBetween('+1 week', '+1 month'),
        ]);
    }

    /**
     * Indicate that the subscription is paused.
     */
    public function paused(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paused',
        ]);
    }

    /**
     * Indicate that the subscription is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }

    /**
     * Indicate that the subscription has weekly billing.
     */
    public function weekly(): static
    {
        return $this->state(fn (array $attributes) => [
            'billing_cycle' => 'weekly',
        ]);
    }

    /**
     * Indicate that the subscription has monthly billing.
     */
    public function monthly(): static
    {
        return $this->state(fn (array $attributes) => [
            'billing_cycle' => 'monthly',
        ]);
    }

    /**
     * Indicate that the subscription has annual billing.
     */
    public function annual(): static
    {
        return $this->state(fn (array $attributes) => [
            'billing_cycle' => 'annual',
        ]);
    }
}
