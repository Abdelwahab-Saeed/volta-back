<?php

namespace Database\Factories;

use App\Models\Category;
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
            'category_id' => Category::factory(),
            'name_ar' => $this->faker->words(3, true),
            'name_en' => $this->faker->words(3, true),
            'description_ar' => $this->faker->sentence(),
            'description_en' => $this->faker->sentence(),
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'discount' => $this->faker->randomFloat(2, 0, 50),
            'stock' => $this->faker->numberBetween(1, 100),
            'image' => $this->faker->imageUrl(),
            'status' => true,
        ];
    }
}
