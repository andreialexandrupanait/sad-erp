<?php

namespace Database\Factories;

use App\Models\BankingCredential;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BankingCredential>
 */
class BankingCredentialFactory extends Factory
{
    protected $model = BankingCredential::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'user_id' => User::factory(),
            'bank_name' => fake()->randomElement(['ING Bank', 'BCR', 'BRD', 'Raiffeisen Bank', 'Transilvania Bank']),
            'account_iban' => fake()->iban('RO'),
            'account_name' => fake()->words(3, true) . ' Account',
            'currency' => fake()->randomElement(['RON', 'EUR', 'USD']),
            'consent_status' => 'active',
            'consent_granted_at' => now(),
            'consent_expires_at' => now()->addDays(90),
            'consent_scopes' => ['balances', 'transactions'],
            'status' => 'active',
            'consecutive_failures' => 0,
        ];
    }

    public function active(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'active',
            'consent_status' => 'active',
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'inactive',
            'consent_status' => 'revoked',
        ]);
    }

    public function error(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'error',
            'consecutive_failures' => 3,
            'error_message' => 'Token expired',
        ]);
    }

    public function expiredConsent(): static
    {
        return $this->state(fn(array $attributes) => [
            'consent_expires_at' => now()->subDays(1),
        ]);
    }
}
