<?php

namespace Gopos\Database\Factories;

use Gopos\Models\Department;
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
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'birth_date' => $this->faker->dateTimeBetween('-50 years', '-20 years'),
            'gender' => $this->faker->randomElement(['male', 'female']),
            'national_id' => $this->faker->unique()->numerify('############'),
            'address' => $this->faker->address(),
            'hire_date' => $this->faker->dateTimeBetween('-5 years', 'now'),
            'basic_salary' => $this->faker->randomFloat(2, 500, 5000),
            'status' => 'active',
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'terminated',
        ]);
    }

    public function withDepartment(): static
    {
        return $this->state(fn (array $attributes) => [
            'department_id' => Department::factory(),
        ]);
    }
}
