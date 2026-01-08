<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Offer;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Offer>
 */
class OfferFactory extends Factory
{
    protected $model = Offer::class;

    public function definition(): array
    {
        $year = date('Y');
        $subtotal = fake()->randomFloat(2, 500, 10000);
        $discountPercent = fake()->randomElement([0, 5, 10, 15, 20]);
        $discountAmount = $subtotal * ($discountPercent / 100);
        $total = $subtotal - $discountAmount;

        return [
            'organization_id' => Organization::factory(),
            'client_id' => Client::factory(),
            'created_by_user_id' => User::factory(),
            'offer_number' => fn() => sprintf('OFR-%d-%02d', $year, fake()->unique()->numberBetween(1, 999)),
            'title' => fake()->sentence(4),
            'status' => 'draft',
            'valid_until' => now()->addDays(30),
            'public_token' => Str::random(64),
            'subtotal' => $subtotal,
            'discount_percent' => $discountPercent,
            'discount_amount' => $discountAmount,
            'total' => $total,
            'currency' => fake()->randomElement(['EUR', 'RON', 'USD']),
            'current_version' => 1,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'draft',
        ]);
    }

    public function sent(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    public function viewed(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'viewed',
            'sent_at' => now()->subDays(2),
            'viewed_at' => now()->subDay(),
        ]);
    }

    public function accepted(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'accepted',
            'sent_at' => now()->subDays(5),
            'viewed_at' => now()->subDays(3),
            'accepted_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'rejected',
            'sent_at' => now()->subDays(5),
            'viewed_at' => now()->subDays(3),
            'rejected_at' => now(),
            'rejection_reason' => fake()->sentence(),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'expired',
            'valid_until' => now()->subDays(5),
        ]);
    }

    public function withTempClient(): static
    {
        return $this->state(fn(array $attributes) => [
            'client_id' => null,
            'temp_client_name' => fake()->name(),
            'temp_client_email' => fake()->email(),
            'temp_client_company' => fake()->company(),
            'temp_client_phone' => fake()->phoneNumber(),
        ]);
    }

    public function withContract(): static
    {
        return $this->state(fn(array $attributes) => [
            'contract_id' => \App\Models\Contract::factory(),
        ]);
    }
}
