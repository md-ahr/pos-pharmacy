<?php

use App\Enums\PurchaseOrderStatus;
use App\Livewire\Inventory\ProductForm;
use App\Models\Batch;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Manufacturer;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Stock;
use App\Models\Supplier;
use App\Services\PurchaseOrderService;
use App\Services\StockIntakeService;
use App\Services\StockTransferService;
use Database\Seeders\PharmacyRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(PharmacyRoleSeeder::class);
});

test('owner can access inventory screens', function () {
    createPharmacyContext();

    $this->get(route('pharmacy.inventory'))->assertOk();
    $this->get(route('pharmacy.inventory.products'))->assertOk();
    $this->get(route('pharmacy.inventory.suppliers'))->assertOk();
    $this->get(route('pharmacy.inventory.categories'))->assertOk();
    $this->get(route('pharmacy.inventory.manufacturers'))->assertOk();
    $this->get(route('pharmacy.inventory.purchase-orders'))->assertOk();
});

test('products index shows cost and sell price columns', function () {
    ['tenant' => $tenant] = createPharmacyContext();

    $product = Product::factory()->forTenant($tenant)->create([
        'name' => 'Ibuprofen 200mg',
        'sku' => 'IBU-200',
    ]);

    ProductUnit::factory()->create([
        'product_id' => $product->id,
        'unit_name' => 'tablet',
        'conversion_factor' => 1,
        'selling_price' => '12.50',
        'is_default' => true,
    ]);

    Batch::factory()->forProduct($product)->create([
        'tenant_id' => $tenant->id,
        'cost_price' => '8.75',
        'received_at' => now(),
    ]);

    $this->get(route('pharmacy.inventory.products'))
        ->assertOk()
        ->assertSee('Cost')
        ->assertSee('Sell Price')
        ->assertSee('8.75')
        ->assertSee('12.50');
});

test('product form creates product with units', function () {
    ['tenant' => $tenant] = createPharmacyContext();
    $category = Category::factory()->create(['tenant_id' => $tenant->id]);
    $manufacturer = Manufacturer::factory()->create(['tenant_id' => $tenant->id]);

    Livewire::test(ProductForm::class)
        ->set('name', 'Ibuprofen 200mg')
        ->set('sku', 'IBU-200')
        ->set('base_unit', 'tablet')
        ->set('category_id', $category->id)
        ->set('manufacturer_id', $manufacturer->id)
        ->set('reorder_level', 25)
        ->set('units', [
            ['unit_name' => 'tablet', 'conversion_factor' => 1, 'barcode' => '', 'selling_price' => '2.00', 'is_default' => true],
            ['unit_name' => 'strip', 'conversion_factor' => 10, 'barcode' => '', 'selling_price' => '18.00', 'is_default' => false],
        ])
        ->call('save')
        ->assertRedirect(route('pharmacy.inventory.products'));

    $product = Product::query()->where('sku', 'IBU-200')->first();

    expect($product)->not->toBeNull()
        ->and($product->units)->toHaveCount(2);
});

test('product form explains sell price against latest batch cost', function () {
    ['tenant' => $tenant] = createPharmacyContext();

    $product = Product::factory()->forTenant($tenant)->create([
        'name' => 'Insulin Pen',
        'base_unit' => 'unit',
    ]);

    ProductUnit::factory()->create([
        'product_id' => $product->id,
        'unit_name' => 'pack',
        'conversion_factor' => 1,
        'selling_price' => '65.00',
        'is_default' => true,
    ]);

    Batch::factory()->forProduct($product)->create([
        'tenant_id' => $tenant->id,
        'cost_price' => '35.00',
        'received_at' => now(),
    ]);

    Livewire::test(ProductForm::class, ['product' => $product])
        ->assertSee('Pricing Guide')
        ->assertSee('Latest batch cost for 1 unit')
        ->assertSee('35.00')
        ->assertSee('Selling Price (per pack)')
        ->assertSee('Profit:')
        ->assertSee('30.00');
});

test('batch intake increases branch stock', function () {
    ['tenant' => $tenant, 'branch' => $branch] = createPharmacyContext();
    $product = Product::factory()->forTenant($tenant)->create();

    app(StockIntakeService::class)->intake(
        branch: $branch,
        product: $product,
        batchNo: 'INTAKE-001',
        expiryDate: now()->addYear(),
        costPrice: '4.50',
        sellingPrice: '7.00',
        quantityBase: 100,
    );

    expect(Stock::query()->where('branch_id', $branch->id)->sum('quantity'))->toBe(100)
        ->and(Batch::query()->where('batch_no', 'INTAKE-001')->exists())->toBeTrue();
});

test('purchase order receive workflow creates stock', function () {
    ['tenant' => $tenant, 'branch' => $branch, 'user' => $user] = createPharmacyContext();
    $product = Product::factory()->forTenant($tenant)->create();
    $supplier = Supplier::factory()->create(['tenant_id' => $tenant->id]);
    $service = app(PurchaseOrderService::class);

    $po = $service->createDraft($branch, $supplier, [
        ['product_id' => $product->id, 'quantity' => 40, 'unit_cost' => '3.00'],
    ], $user);

    $service->markOrdered($po);

    $item = $po->fresh()->items->first();

    $service->receive($po->fresh(), [[
        'purchase_order_item_id' => $item->id,
        'batch_no' => 'PO-BATCH-1',
        'expiry_date' => now()->addMonths(8)->toDateString(),
        'selling_price' => '6.00',
    ]]);

    expect($po->fresh()->status)->toBe(PurchaseOrderStatus::Received)
        ->and(Stock::query()->where('branch_id', $branch->id)->sum('quantity'))->toBe(40);
});

test('stock transfer moves quantity between branches', function () {
    ['tenant' => $tenant, 'branch' => $fromBranch] = createPharmacyContext();
    $toBranch = Branch::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Branch B']);
    $product = Product::factory()->forTenant($tenant)->create();
    $batch = Batch::factory()->forProduct($product)->create(['tenant_id' => $tenant->id]);

    Stock::factory()->create([
        'tenant_id' => $tenant->id,
        'branch_id' => $fromBranch->id,
        'product_id' => $product->id,
        'batch_id' => $batch->id,
        'quantity' => 50,
    ]);

    app(StockTransferService::class)->initiate(
        fromBranch: $fromBranch,
        toBranch: $toBranch,
        product: $product,
        batch: $batch,
        quantity: 20,
    );

    expect(Stock::query()->where('branch_id', $fromBranch->id)->sum('quantity'))->toBe(30)
        ->and(Stock::query()->where('branch_id', $toBranch->id)->sum('quantity'))->toBe(20);
});
