<?php

namespace Database\Factories;

use App\Enums\SaleStatus;
use App\Models\Branch;
use App\Models\Sale;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sale>
 */
class SaleFactory extends Factory
{
    protected $model = Sale::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 10, 500);
        $discount = 0;
        $tax = round($subtotal * 0.15, 2);
        $total = $subtotal - $discount + $tax;

        return [
            'tenant_id' => Tenant::factory(),
            'branch_id' => Branch::factory(),
            'user_id' => User::factory(),
            'customer_id' => null,
            'invoice_no' => fake()->unique()->numerify('INV-######'),
            'subtotal' => $subtotal,
            'discount_amount' => $discount,
            'tax_amount' => $tax,
            'total' => $total,
            'paid_amount' => $total,
            'change_amount' => 0,
            'status' => SaleStatus::Completed,
            'prescription_required' => false,
            'prescriber_name' => null,
            'prescriber_reg_no' => null,
            'sold_at' => now(),
        ];
    }
}
