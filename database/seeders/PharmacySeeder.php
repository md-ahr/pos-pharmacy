<?php

namespace Database\Seeders;

use App\Models\Batch;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Manufacturer;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Stock;
use App\Models\Supplier;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class PharmacySeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::factory()->create([
            'name' => 'Demo Pharmacy',
            'slug' => 'demo-pharmacy',
            'email' => 'demo@pharmacy.test',
        ]);

        $branch = Branch::factory()->main()->create([
            'tenant_id' => $tenant->id,
        ]);

        User::factory()->owner($tenant, $branch)->create([
            'name' => 'Demo Owner',
            'email' => 'owner@pharmacy.test',
        ]);

        $category = Category::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'General Medicines',
        ]);

        $manufacturer = Manufacturer::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Acme Pharma',
        ]);

        Supplier::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'MedSupply Co',
        ]);

        $products = [
            ['name' => 'Paracetamol 500mg', 'generic_name' => 'Paracetamol', 'base_unit' => 'tablet', 'sku' => 'PARA-500'],
            ['name' => 'Amoxicillin 250mg', 'generic_name' => 'Amoxicillin', 'base_unit' => 'capsule', 'sku' => 'AMOX-250', 'requires_prescription' => true],
            ['name' => 'Cough Syrup 100ml', 'generic_name' => 'Dextromethorphan', 'base_unit' => 'ml', 'sku' => 'COUGH-100'],
        ];

        foreach ($products as $productData) {
            $product = Product::factory()->create([
                'tenant_id' => $tenant->id,
                'category_id' => $category->id,
                'manufacturer_id' => $manufacturer->id,
                ...$productData,
            ]);

            ProductUnit::factory()->default()->create([
                'product_id' => $product->id,
            ]);

            if ($product->base_unit === 'tablet') {
                ProductUnit::factory()->create([
                    'product_id' => $product->id,
                    'unit_name' => 'strip',
                    'conversion_factor' => 10,
                    'selling_price' => 25.00,
                ]);
            }

            $batch = Batch::factory()->create([
                'tenant_id' => $tenant->id,
                'product_id' => $product->id,
                'batch_no' => 'BATCH-'.strtoupper(substr($product->sku, 0, 4)),
                'expiry_date' => now()->addMonths(12),
                'cost_price' => 5.00,
                'selling_price' => 10.00,
            ]);

            Stock::factory()->create([
                'tenant_id' => $tenant->id,
                'branch_id' => $branch->id,
                'product_id' => $product->id,
                'batch_id' => $batch->id,
                'quantity' => 500,
            ]);
        }
    }
}
