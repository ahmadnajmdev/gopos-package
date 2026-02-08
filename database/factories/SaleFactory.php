<?php

namespace Gopos\Database\Factories;

use Gopos\Models\Currency;
use Gopos\Models\Sale;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Gopos\Models\Sale>
 */
class SaleFactory extends Factory
{
    protected $model = Sale::class;

    public function definition(): array
    {
        $subTotal = $this->faker->randomFloat(2, 50, 1000);

        return [
            'branch_id' => \Gopos\Models\Branch::factory(),
            'sale_number' => 'INV-'.$this->faker->unique()->numerify('####'),
            'sale_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'currency_id' => Currency::factory(),
            'exchange_rate' => 1,
            'sub_total' => $subTotal,
            'discount' => 0,
            'tax_amount' => 0,
            'total_amount' => $subTotal,
            'paid_amount' => $subTotal,
            'amount_in_base_currency' => $subTotal,
            'status' => 'paid',
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'paid_amount' => 0,
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
        ]);
    }

    public function partial(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'partial',
        ]);
    }
}
