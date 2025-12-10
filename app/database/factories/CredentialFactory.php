<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Credential;
use App\Models\Organization;
use App\Models\SettingOption;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Credential>
 */
class CredentialFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Credential::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $platforms = array_keys(Credential::PLATFORMS);

        return [
            'organization_id' => Organization::factory(),
            'client_id' => Client::factory(),
            'platform' => fake()->randomElement($platforms),
            'username' => fake()->userName(),
            'password' => fake()->password(12, 20),
            'url' => fake()->optional(0.7)->url(),
            'notes' => fake()->optional(0.3)->sentence(),
            'last_accessed_at' => null,
            'access_count' => 0,
        ];
    }

    /**
     * Indicate that the credential is for a specific platform.
     */
    public function forPlatform(string $platform): static
    {
        return $this->state(fn (array $attributes) => [
            'platform' => $platform,
        ]);
    }

    /**
     * Indicate that the credential has been accessed recently.
     */
    public function recentlyAccessed(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_accessed_at' => fake()->dateTimeBetween('-7 days', 'now'),
            'access_count' => fake()->numberBetween(1, 50),
        ]);
    }

    /**
     * Indicate that the credential is for WordPress.
     */
    public function wordpress(): static
    {
        return $this->state(fn (array $attributes) => [
            'platform' => 'WordPress',
            'url' => fake()->url() . '/wp-admin',
        ]);
    }

    /**
     * Indicate that the credential is for cPanel.
     */
    public function cpanel(): static
    {
        return $this->state(fn (array $attributes) => [
            'platform' => 'cPanel',
            'url' => 'https://' . fake()->domainName() . ':2083',
        ]);
    }

    /**
     * Indicate that the credential is for Google services.
     */
    public function google(): static
    {
        return $this->state(fn (array $attributes) => [
            'platform' => fake()->randomElement(['Google Ads', 'Google Analytics']),
            'username' => fake()->email(),
        ]);
    }

    /**
     * Indicate that the credential is for social media.
     */
    public function socialMedia(): static
    {
        return $this->state(fn (array $attributes) => [
            'platform' => fake()->randomElement(['Facebook', 'Instagram', 'Twitter/X', 'LinkedIn', 'TikTok', 'YouTube']),
        ]);
    }
}
