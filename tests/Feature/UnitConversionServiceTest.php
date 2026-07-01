<?php

use App\Models\Category;
use App\Models\Manufacturer;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Services\UnitConversionService;
use Database\Seeders\PharmacyRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(PharmacyRoleSeeder::class);
});

test('converts sell units to base units using conversion factor', function () {
    ['tenant' => $tenant, 'branch' => $branch] = createPharmacyContext();

    $category = Category::factory()->create(['tenant_id' => $tenant->id]);
    $manufacturer = Manufacturer::factory()->create(['tenant_id' => $tenant->id]);
    $product = Product::factory()->create([
        'tenant_id' => $tenant->id,
        'category_id' => $category->id,
        'manufacturer_id' => $manufacturer->id,
        'base_unit' => 'tablet',
    ]);

    $strip = ProductUnit::factory()->create([
        'product_id' => $product->id,
        'unit_name' => 'strip',
        'conversion_factor' => 10,
    ]);

    $service = app(UnitConversionService::class);

    expect($service->toBaseUnits($product, null, 5))->toBe(5)
        ->and($service->toBaseUnits($product, $strip, 3))->toBe(30);
});

test('rejects unit that does not belong to product', function () {
    ['tenant' => $tenant] = createPharmacyContext();

    $productA = Product::factory()->forTenant($tenant)->create();
    $productB = Product::factory()->forTenant($tenant)->create();
    $unit = ProductUnit::factory()->create(['product_id' => $productB->id]);

    app(UnitConversionService::class)->toBaseUnits($productA, $unit, 1);
})->throws(InvalidArgumentException::class);
