<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductUnit>
 */
class ProductUnitFactory extends Factory
{
    protected $model = ProductUnit::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'unit_name' => fake()->randomElement(['strip', 'box', 'bottle']),
            'conversion_factor' => fake()->randomElement([10, 20, 100]),
            'barcode' => fake()->optional()->ean13(),
            'selling_price' => fake()->randomFloat(2, 5, 500),
            'is_default' => false,
        ];
    }

    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
            'unit_name' => 'unit',
            'conversion_factor' => 1,
        ]);
    }
}
