<?php

namespace Gopos\Database\Factories;

use Gopos\Models\Employee;
use Gopos\Models\PayrollPeriod;
use Gopos\Models\Payslip;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Gopos\Models\Payslip>
 */
class PayslipFactory extends Factory
{
    protected $model = Payslip::class;

    public function definition(): array
    {
        $basicSalary = $this->faker->randomFloat(2, 1000, 5000);

        return [
            'payroll_period_id' => PayrollPeriod::factory(),
            'employee_id' => Employee::factory(),
            'payslip_number' => 'PS-'.$this->faker->unique()->numerify('######'),
            'basic_salary' => $basicSalary,
            'gross_salary' => $basicSalary,
            'total_earnings' => $basicSalary,
            'total_deductions' => 0,
            'net_salary' => $basicSalary,
            'working_days' => 30,
            'days_worked' => 30,
            'status' => 'draft',
        ];
    }

    public function processed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'processed',
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
        ]);
    }
}
