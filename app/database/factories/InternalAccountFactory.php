<?php

namespace Database\Factories;

use App\Models\InternalAccount;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InternalAccount>
 */
class InternalAccountFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = InternalAccount::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'user_id' => User::factory(),
            'account_name' => fake()->company() . ' ' . fake()->randomElement(['Admin', 'Portal', 'Dashboard', 'Account']),
            'url' => fake()->optional(0.8)->url(),
            'username' => fake()->userName(),
            'password' => fake()->password(12, 20),
            'team_accessible' => fake()->boolean(30), // 30% chance of being team accessible
            'notes' => fake()->optional(0.3)->sentence(),
        ];
    }

    /**
     * Indicate that the account is team accessible.
     */
    public function teamAccessible(): static
    {
        return $this->state(fn (array $attributes) => [
            'team_accessible' => true,
        ]);
    }

    /**
     * Indicate that the account is private (not team accessible).
     */
    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'team_accessible' => false,
        ]);
    }

    /**
     * Indicate that the account belongs to a specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
            'organization_id' => $user->organization_id,
        ]);
    }
}
