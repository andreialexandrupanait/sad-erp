<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Domain;
use App\Models\Organization;
use App\Models\SettingOption;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Domain>
 */
class DomainFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Domain::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $extensions = ['.ro', '.com', '.net', '.org', '.eu', '.io'];
        $domainName = fake()->domainWord() . fake()->randomElement($extensions);

        return [
            'organization_id' => Organization::factory(),
            'client_id' => Client::factory(),
            'domain_name' => $domainName,
            'registration_date' => fake()->dateTimeBetween('-2 years', '-1 month'),
            'expiry_date' => fake()->dateTimeBetween('+1 month', '+2 years'),
            'renewal_cost' => fake()->randomFloat(2, 10, 100),
            'currency' => fake()->randomElement(['RON', 'EUR', 'USD']),
            'registrar_id' => null, // Will be set by afterCreating
            'status_id' => null, // Will be set by afterCreating
            'auto_renew' => fake()->boolean(70), // 70% chance of auto-renew
            'notes' => fake()->optional()->sentence(),
        ];
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Domain $domain) {
            // Set a random registrar if not already set
            if (!$domain->registrar_id) {
                $registrars = SettingOption::domainRegistrars()->pluck('id')->toArray();
                if (!empty($registrars)) {
                    $domain->update(['registrar_id' => fake()->randomElement($registrars)]);
                }
            }

            // Set a random status if not already set
            if (!$domain->status_id) {
                $statuses = SettingOption::domainStatuses()->pluck('id')->toArray();
                if (!empty($statuses)) {
                    $domain->update(['status_id' => fake()->randomElement($statuses)]);
                }
            }
        });
    }

    /**
     * Indicate that the domain is active.
     */
    public function active(): static
    {
        return $this->state(function (array $attributes) {
            $activeStatus = SettingOption::domainStatuses()
                ->where('slug', 'active')
                ->first();

            return [
                'status_id' => $activeStatus?->id,
                'expiry_date' => fake()->dateTimeBetween('+3 months', '+2 years'),
            ];
        });
    }

    /**
     * Indicate that the domain is expiring soon.
     */
    public function expiringSoon(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'expiry_date' => fake()->dateTimeBetween('now', '+30 days'),
            ];
        });
    }

    /**
     * Indicate that the domain is expired.
     */
    public function expired(): static
    {
        return $this->state(function (array $attributes) {
            $expiredStatus = SettingOption::domainStatuses()
                ->where('slug', 'expired')
                ->first();

            return [
                'status_id' => $expiredStatus?->id,
                'expiry_date' => fake()->dateTimeBetween('-90 days', '-1 day'),
            ];
        });
    }

    /**
     * Indicate that the domain has auto-renew enabled.
     */
    public function autoRenew(): static
    {
        return $this->state(fn (array $attributes) => [
            'auto_renew' => true,
        ]);
    }

    /**
     * Indicate that the domain is registered with a specific registrar.
     */
    public function withRegistrar(string $registrarSlug): static
    {
        return $this->state(function (array $attributes) use ($registrarSlug) {
            $registrar = SettingOption::domainRegistrars()
                ->where('slug', $registrarSlug)
                ->first();

            return [
                'registrar_id' => $registrar?->id,
            ];
        });
    }
}
