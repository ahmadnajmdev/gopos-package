<?php

namespace Gopos\Database\Factories;

use Gopos\Enums\QuotationStatus;
use Gopos\Models\Branch;
use Gopos\Models\Currency;
use Gopos\Models\Quotation;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuotationFactory extends Factory
{
    protected $model = Quotation::class;

    public function definition(): array
    {
        $subTotal = $this->faker->randomFloat(2, 50, 5000);

        return [
            'branch_id' => Branch::factory(),
            'quotation_number' => 'QUO-'.$this->faker->unique()->numerify('#####'),
            'quotation_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'valid_until' => $this->faker->dateTimeBetween('now', '+30 days'),
            'currency_id' => Currency::factory(),
            'exchange_rate' => 1,
            'sub_total' => $subTotal,
            'discount' => 0,
            'total_amount' => $subTotal,
            'amount_in_base_currency' => $subTotal,
            'status' => QuotationStatus::Draft,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => QuotationStatus::Draft,
        ]);
    }

    public function sent(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => QuotationStatus::Sent,
        ]);
    }

    public function accepted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => QuotationStatus::Accepted,
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => QuotationStatus::Rejected,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => QuotationStatus::Expired,
            'valid_until' => $this->faker->dateTimeBetween('-30 days', '-1 day'),
        ]);
    }
}
