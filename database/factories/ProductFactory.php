<?php

namespace Gopos\Database\Factories;

use Gopos\Models\Category;
use Gopos\Models\Product;
use Gopos\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Gopos\Models\Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'branch_id' => \Gopos\Models\Branch::factory(),
            'name' => $this->faker->words(3, true),
            'category_id' => Category::factory(),
            'unit_id' => Unit::factory(),
            'barcode' => $this->faker->unique()->ean13(),
            'description' => $this->faker->sentence(),
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'cost' => $this->faker->randomFloat(2, 5, 500),
            'stock' => $this->faker->numberBetween(0, 100),
            'low_stock_alert' => $this->faker->numberBetween(5, 20),
        ];
    }

    public function lowStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => 3,
            'low_stock_alert' => 10,
        ]);
    }

    public function withBatches(): static
    {
        return $this->state(fn (array $attributes) => [
            'track_batches' => true,
            'has_expiry' => true,
        ]);
    }

    public function withSerials(): static
    {
        return $this->state(fn (array $attributes) => [
            'track_serials' => true,
        ]);
    }
}
