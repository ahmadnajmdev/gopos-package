<?php

namespace Gopos\Database\Factories;

use Gopos\Enums\HolidayType;
use Gopos\Models\Holiday;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Gopos\Models\Holiday>
 */
class HolidayFactory extends Factory
{
    protected $model = Holiday::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'date' => $this->faker->dateTimeBetween('now', '+1 year'),
            'type' => HolidayType::Public,
            'is_recurring' => false,
        ];
    }

    public function recurring(): static
    {
        return $this->state(['is_recurring' => true]);
    }
}
