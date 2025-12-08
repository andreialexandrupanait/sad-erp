<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Organization;
use App\Models\SettingOption;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Client::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->company();

        return [
            'organization_id' => Organization::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'company_name' => $name,
            'contact_person' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'tax_id' => fake()->numerify('RO########'),
            'registration_number' => fake()->numerify('J##/####/####'),
            'address' => fake()->streetAddress(),
            'notes' => fake()->optional()->paragraph(),
            'status_id' => null, // Will be set by afterCreating
        ];
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Client $client) {
            // Set a random client status if not already set
            if (!$client->status_id) {
                $statuses = SettingOption::clientStatuses()->pluck('id')->toArray();
                if (!empty($statuses)) {
                    $client->update(['status_id' => fake()->randomElement($statuses)]);
                }
            }
        });
    }

    /**
     * Indicate that the client is active.
     */
    public function active(): static
    {
        return $this->state(function (array $attributes) {
            $activeStatus = SettingOption::clientStatuses()
                ->where('slug', 'active')
                ->first();

            return [
                'status_id' => $activeStatus?->id,
            ];
        });
    }

    /**
     * Indicate that the client is inactive.
     */
    public function inactive(): static
    {
        return $this->state(function (array $attributes) {
            $inactiveStatus = SettingOption::clientStatuses()
                ->where('slug', 'inactive')
                ->first();

            return [
                'status_id' => $inactiveStatus?->id,
            ];
        });
    }

    /**
     * Indicate that the client has complete information.
     */
    public function complete(): static
    {
        return $this->state(fn (array $attributes) => [
            'notes' => fake()->paragraph(),
        ]);
    }
}
