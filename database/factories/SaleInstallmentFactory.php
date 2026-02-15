<?php

namespace Gopos\Database\Factories;

use Gopos\Enums\InstallmentStatus;
use Gopos\Models\Sale;
use Gopos\Models\SaleInstallment;
use Illuminate\Database\Eloquent\Factories\Factory;

class SaleInstallmentFactory extends Factory
{
    protected $model = SaleInstallment::class;

    public function definition(): array
    {
        return [
            'sale_id' => Sale::factory(),
            'installment_number' => $this->faker->numberBetween(1, 12),
            'due_date' => $this->faker->dateTimeBetween('now', '+6 months'),
            'amount' => $this->faker->randomFloat(2, 50, 500),
            'paid_amount' => 0,
            'status' => InstallmentStatus::Pending,
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => InstallmentStatus::Paid,
            'paid_amount' => $attributes['amount'],
            'paid_date' => now(),
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => InstallmentStatus::Overdue,
            'due_date' => $this->faker->dateTimeBetween('-30 days', '-1 day'),
        ]);
    }

    public function partial(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => InstallmentStatus::Partial,
            'paid_amount' => $this->faker->randomFloat(2, 1, $attributes['amount'] - 1),
        ]);
    }
}
