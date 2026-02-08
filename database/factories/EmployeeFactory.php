<?php

namespace Gopos\Database\Factories;

use Gopos\Enums\EmployeeStatus;
use Gopos\Enums\Gender;
use Gopos\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Gopos\Models\Employee>
 */
class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        return [
            'branch_id' => \Gopos\Models\Branch::factory(),
            'employee_number' => 'EMP-'.$this->faker->unique()->numerify('####'),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->numerify('07#########'),
            'date_of_birth' => $this->faker->dateTimeBetween('-50 years', '-18 years'),
            'gender' => $this->faker->randomElement(Gender::cases()),
            'hire_date' => $this->faker->dateTimeBetween('-5 years', 'now'),
            'salary' => $this->faker->numberBetween(500, 5000),
            'status' => EmployeeStatus::Active,
        ];
    }

    public function terminated(): static
    {
        return $this->state(['status' => EmployeeStatus::Terminated]);
    }

    public function onLeave(): static
    {
        return $this->state(['status' => EmployeeStatus::OnLeave]);
    }

    public function suspended(): static
    {
        return $this->state(['status' => EmployeeStatus::Suspended]);
    }
}
