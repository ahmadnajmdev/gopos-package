<?php

namespace Gopos\Database\Factories;

use Gopos\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Gopos\Models\Department>
 */
class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->randomElement([
                'Sales', 'Marketing', 'Finance', 'HR', 'IT',
                'Operations', 'Administration', 'Warehouse', 'Customer Service',
            ]),
            'name_ar' => null,
            'name_ckb' => null,
            'code' => strtoupper($this->faker->unique()->bothify('DEPT-###')),
            'is_active' => true,
        ];
    }
}
