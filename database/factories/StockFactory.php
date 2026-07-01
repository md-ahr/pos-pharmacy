<?php

namespace Database\Factories;

use App\Models\Batch;
use App\Models\Branch;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Stock>
 */
class StockFactory extends Factory
{
    protected $model = Stock::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'branch_id' => Branch::factory(),
            'product_id' => Product::factory(),
            'batch_id' => Batch::factory(),
            'quantity' => fake()->numberBetween(50, 500),
        ];
    }

    public function forBatch(Batch $batch, Branch $branch): static
    {
        return $this->state(fn (array $attributes) => [
            'tenant_id' => $batch->tenant_id,
            'branch_id' => $branch->id,
            'product_id' => $batch->product_id,
            'batch_id' => $batch->id,
        ]);
    }
}
