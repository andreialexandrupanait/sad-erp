<?php

namespace Database\Factories;

use App\Models\Offer;
use App\Models\OfferItem;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OfferItem>
 */
class OfferItemFactory extends Factory
{
    protected $model = OfferItem::class;

    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 10);
        $unitPrice = fake()->randomFloat(2, 50, 500);
        $discountPercent = fake()->randomElement([0, 5, 10, 15]);
        $discountAmount = ($quantity * $unitPrice) * ($discountPercent / 100);
        $totalPrice = ($quantity * $unitPrice) - $discountAmount;

        return [
            'offer_id' => Offer::factory(),
            'service_id' => null,
            'name' => fake()->sentence(3),
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'quantity' => $quantity,
            'unit' => fake()->randomElement(['unit', 'hour', 'month', 'project']),
            'unit_price' => $unitPrice,
            'discount_percent' => $discountPercent,
            'discount_amount' => $discountAmount,
            'total_price' => $totalPrice,
            'is_selected' => true,
            'sort_order' => fake()->numberBetween(0, 10),
        ];
    }

    public function selected(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_selected' => true,
        ]);
    }

    public function notSelected(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_selected' => false,
        ]);
    }

    public function withService(Service $service = null): static
    {
        return $this->state(fn(array $attributes) => [
            'service_id' => $service?->id ?? Service::factory(),
        ]);
    }
}
