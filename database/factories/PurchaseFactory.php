<?php

namespace Gopos\Database\Factories;

use Gopos\Models\Currency;
use Gopos\Models\Purchase;
use Gopos\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Gopos\Models\Purchase>
 */
class PurchaseFactory extends Factory
{
    protected $model = Purchase::class;

    public function definition(): array
    {
        $subTotal = $this->faker->randomFloat(2, 100, 5000);

        return [
            'branch_id' => \Gopos\Models\Branch::factory(),
            'purchase_number' => 'PUR-'.$this->faker->unique()->numerify('#####'),
            'purchase_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'supplier_id' => Supplier::factory(),
            'currency_id' => Currency::factory(),
            'exchange_rate' => 1,
            'sub_total' => $subTotal,
            'discount_amount' => 0,
            'total_amount' => $subTotal,
            'paid_amount' => 0,
            'amount_in_base_currency' => $subTotal,
        ];
    }

    public function paid(): static
    {
        return $this->afterMaking(function (Purchase $purchase) {
            $purchase->paid_amount = $purchase->total_amount;
        })->afterCreating(function (Purchase $purchase) {
            $purchase->update(['paid_amount' => $purchase->total_amount]);
        });
    }

    public function partial(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'paid_amount' => $attributes['total_amount'] / 2,
            ];
        });
    }
}
