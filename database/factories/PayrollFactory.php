<?php

namespace Gopos\Database\Factories;

use Gopos\Enums\PayrollStatus;
use Gopos\Models\Employee;
use Gopos\Models\Payroll;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Gopos\Models\Payroll>
 */
class PayrollFactory extends Factory
{
    protected $model = Payroll::class;

    public function definition(): array
    {
        $basicSalary = $this->faker->numberBetween(500, 5000);
        $deductions = $this->faker->numberBetween(0, 200);
        $bonuses = $this->faker->numberBetween(0, 500);
        $overtimePay = $this->faker->numberBetween(0, 300);

        return [
            'employee_id' => Employee::factory(),
            'pay_period_start' => now()->startOfMonth(),
            'pay_period_end' => now()->endOfMonth(),
            'basic_salary' => $basicSalary,
            'deductions' => $deductions,
            'bonuses' => $bonuses,
            'overtime_pay' => $overtimePay,
            'net_pay' => $basicSalary + $bonuses + $overtimePay - $deductions,
            'status' => PayrollStatus::Draft,
        ];
    }

    public function processed(): static
    {
        return $this->state(['status' => PayrollStatus::Processed]);
    }

    public function paid(): static
    {
        return $this->state([
            'status' => PayrollStatus::Paid,
            'paid_at' => now(),
        ]);
    }
}
