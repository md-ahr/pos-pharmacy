<?php

namespace Database\Factories;

use App\Models\Batch;
use App\Models\Product;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Batch>
 */
class BatchFactory extends Factory
{
    protected $model = Batch::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'product_id' => Product::factory(),
            'batch_no' => strtoupper(fake()->bothify('BATCH-####')),
            'expiry_date' => fake()->dateTimeBetween('+3 months', '+2 years'),
            'cost_price' => fake()->randomFloat(2, 1, 100),
            'selling_price' => fake()->randomFloat(2, 2, 150),
            'received_at' => now(),
        ];
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expiry_date' => fake()->dateTimeBetween('-1 year', '-1 day'),
        ]);
    }

    public function forProduct(Product $product): static
    {
        return $this->state(fn (array $attributes) => [
            'tenant_id' => $product->tenant_id,
            'product_id' => $product->id,
        ]);
    }
}
