<?php

namespace Database\Factories;

use App\Enums\StockTransferStatus;
use App\Models\Batch;
use App\Models\Branch;
use App\Models\Product;
use App\Models\StockTransfer;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockTransfer>
 */
class StockTransferFactory extends Factory
{
    protected $model = StockTransfer::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'from_branch_id' => Branch::factory(),
            'to_branch_id' => Branch::factory(),
            'product_id' => Product::factory(),
            'batch_id' => Batch::factory(),
            'quantity' => fake()->numberBetween(10, 100),
            'status' => StockTransferStatus::Pending,
            'initiated_by' => User::factory(),
            'transferred_at' => null,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
