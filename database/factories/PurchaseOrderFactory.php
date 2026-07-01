<?php

namespace Database\Factories;

use App\Enums\PurchaseOrderStatus;
use App\Models\Branch;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PurchaseOrder>
 */
class PurchaseOrderFactory extends Factory
{
    protected $model = PurchaseOrder::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'branch_id' => Branch::factory(),
            'supplier_id' => Supplier::factory(),
            'created_by' => User::factory(),
            'reference_no' => fake()->unique()->numerify('PO-######'),
            'status' => PurchaseOrderStatus::Draft,
            'total_amount' => fake()->randomFloat(2, 100, 5000),
            'ordered_at' => null,
            'received_at' => null,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
