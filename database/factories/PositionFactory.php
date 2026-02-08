<?php

namespace Gopos\Database\Factories;

use Gopos\Models\Position;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Gopos\Models\Position>
 */
class PositionFactory extends Factory
{
    protected $model = Position::class;

    public function definition(): array
    {
        $minSalary = $this->faker->numberBetween(500, 3000);

        return [
            'title' => $this->faker->unique()->jobTitle(),
            'min_salary' => $minSalary,
            'max_salary' => $minSalary + $this->faker->numberBetween(500, 2000),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
