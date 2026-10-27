<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'price' => fake()->numberBetween(100, 10000),
            'description' => fake()->sentence(),
            'amount' => fake()->numberBetween(1, 100),
            'category_id' => Category::factory(),
        ];
    }
}
