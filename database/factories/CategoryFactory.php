<?php

namespace Gopos\Database\Factories;

use Gopos\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Gopos\Models\Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'branch_id' => \Gopos\Models\Branch::factory(),
            'name' => $this->faker->unique()->randomElement([
                'Electronics', 'Food & Beverages', 'Clothing', 'Home & Garden',
                'Sports', 'Books', 'Toys', 'Health', 'Beauty', 'Automotive',
            ]),
        ];
    }
}
