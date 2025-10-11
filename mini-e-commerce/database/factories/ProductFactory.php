<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
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
            'product_name' => fake()->words(3, true),
            'description' => fake()->sentence(10),
            'price' => fake()->numberBetween(10000, 1000000),
            'stock' => fake()->numberBetween(1, 100),
            'photo_product' => 'default-product.jpg',
        ];
    }
}
