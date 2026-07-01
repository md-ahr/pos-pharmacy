<?php

namespace Database\Factories;

use App\Models\Batch;
use App\Models\Branch;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockAdjustment>
 */
class StockAdjustmentFactory extends Factory
{
    protected $model = StockAdjustment::class;

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
            'quantity_delta' => fake()->numberBetween(-20, 20),
            'reason' => fake()->randomElement(['damage', 'expiry_write_off', 'physical_count', 'other']),
            'adjusted_by' => User::factory(),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
