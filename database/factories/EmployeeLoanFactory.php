<?php

namespace Gopos\Database\Factories;

use Gopos\Models\Employee;
use Gopos\Models\EmployeeLoan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Gopos\Models\EmployeeLoan>
 */
class EmployeeLoanFactory extends Factory
{
    protected $model = EmployeeLoan::class;

    public function definition(): array
    {
        $amount = $this->faker->randomFloat(2, 500, 5000);
        $installments = $this->faker->numberBetween(3, 12);

        return [
            'employee_id' => Employee::factory(),
            'loan_amount' => $amount,
            'monthly_deduction' => round($amount / $installments, 2),
            'total_installments' => $installments,
            'paid_installments' => 0,
            'remaining_amount' => $amount,
            'start_date' => now()->format('Y-m-d'),
            'status' => 'active',
            'reason' => $this->faker->sentence(),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'paid_installments' => $attributes['total_installments'],
            'remaining_amount' => 0,
        ]);
    }
}
