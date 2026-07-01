<?php

namespace Database\Factories;

use App\Models\Batch;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SaleItem>
 */
class SaleItemFactory extends Factory
{
    protected $model = SaleItem::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 10);
        $unitPrice = fake()->randomFloat(2, 1, 50);
        $lineTotal = round($quantity * $unitPrice, 2);

        return [
            'sale_id' => Sale::factory(),
            'product_id' => Product::factory(),
            'batch_id' => Batch::factory(),
            'product_unit_id' => null,
            'quantity' => $quantity,
            'quantity_base' => $quantity,
            'unit_price' => $unitPrice,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'line_total' => $lineTotal,
            'is_prescription_item' => false,
        ];
    }
}
