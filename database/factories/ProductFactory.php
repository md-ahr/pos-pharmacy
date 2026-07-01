<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Manufacturer;
use App\Models\Product;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'category_id' => Category::factory(),
            'manufacturer_id' => Manufacturer::factory(),
            'name' => fake()->words(3, true),
            'generic_name' => fake()->optional()->words(2, true),
            'barcode' => fake()->optional()->ean13(),
            'sku' => strtoupper(fake()->bothify('SKU-####')),
            'base_unit' => fake()->randomElement(['tablet', 'ml', 'capsule', 'unit']),
            'reorder_level' => fake()->numberBetween(10, 50),
            'requires_prescription' => false,
            'is_active' => true,
        ];
    }

    public function prescription(): static
    {
        return $this->state(fn (array $attributes) => [
            'requires_prescription' => true,
        ]);
    }

    public function forTenant(Tenant $tenant): static
    {
        return $this->state(fn (array $attributes) => [
            'tenant_id' => $tenant->id,
            'category_id' => Category::factory()->for($tenant),
            'manufacturer_id' => Manufacturer::factory()->for($tenant),
        ]);
    }
}
