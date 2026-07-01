<?php

use App\Exceptions\ExpiredStockException;
use App\Exceptions\InsufficientStockException;
use App\Models\Batch;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Manufacturer;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Tenant;
use App\Services\StockDeductionService;
use Database\Seeders\PharmacyRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(PharmacyRoleSeeder::class);
});

function seedProductWithBatches(Tenant $tenant, Branch $branch, array $batches): Product
{
    $category = Category::factory()->create(['tenant_id' => $tenant->id]);
    $manufacturer = Manufacturer::factory()->create(['tenant_id' => $tenant->id]);
    $product = Product::factory()->create([
        'tenant_id' => $tenant->id,
        'category_id' => $category->id,
        'manufacturer_id' => $manufacturer->id,
        'name' => 'Test Medicine',
    ]);

    foreach ($batches as $batchData) {
        $batch = Batch::factory()->forProduct($product)->create([
            'tenant_id' => $tenant->id,
            'batch_no' => $batchData['batch_no'],
            'expiry_date' => $batchData['expiry_date'],
        ]);

        Stock::factory()->create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'product_id' => $product->id,
            'batch_id' => $batch->id,
            'quantity' => $batchData['quantity'],
        ]);
    }

    return $product;
}

test('deducts stock using fefo order', function () {
    ['tenant' => $tenant, 'branch' => $branch] = createPharmacyContext();

    $product = seedProductWithBatches($tenant, $branch, [
        ['batch_no' => 'LATE', 'expiry_date' => now()->addMonths(6), 'quantity' => 20],
        ['batch_no' => 'SOON', 'expiry_date' => now()->addMonth(), 'quantity' => 15],
    ]);

    $service = app(StockDeductionService::class);
    $deductions = $service->deduct($branch, $product, 10);

    expect($deductions)->toHaveCount(1)
        ->and($deductions->first()->quantityDeducted)->toBe(10);

    $soonBatch = Batch::query()->where('batch_no', 'SOON')->first();
    expect(Stock::query()->where('batch_id', $soonBatch->id)->value('quantity'))->toBe(5)
        ->and(Stock::query()->whereHas('batch', fn ($q) => $q->where('batch_no', 'LATE'))->value('quantity'))->toBe(20);
});

test('spans multiple batches when first batch is insufficient', function () {
    ['tenant' => $tenant, 'branch' => $branch] = createPharmacyContext();

    $product = seedProductWithBatches($tenant, $branch, [
        ['batch_no' => 'A', 'expiry_date' => now()->addMonth(), 'quantity' => 5],
        ['batch_no' => 'B', 'expiry_date' => now()->addMonths(2), 'quantity' => 10],
    ]);

    $deductions = app(StockDeductionService::class)->deduct($branch, $product, 12);

    expect($deductions)->toHaveCount(2)
        ->and($deductions->sum(fn ($line) => $line->quantityDeducted))->toBe(12)
        ->and(Stock::query()->whereHas('batch', fn ($q) => $q->where('batch_no', 'A'))->value('quantity'))->toBe(0)
        ->and(Stock::query()->whereHas('batch', fn ($q) => $q->where('batch_no', 'B'))->value('quantity'))->toBe(3);
});

test('blocks deduction when only expired stock remains', function () {
    ['tenant' => $tenant, 'branch' => $branch] = createPharmacyContext();

    $product = seedProductWithBatches($tenant, $branch, [
        ['batch_no' => 'OLD', 'expiry_date' => now()->subDay(), 'quantity' => 50],
    ]);

    app(StockDeductionService::class)->deduct($branch, $product, 1);
})->throws(ExpiredStockException::class);

test('throws when stock is insufficient', function () {
    ['tenant' => $tenant, 'branch' => $branch] = createPharmacyContext();

    $product = seedProductWithBatches($tenant, $branch, [
        ['batch_no' => 'OK', 'expiry_date' => now()->addMonths(3), 'quantity' => 5],
    ]);

    app(StockDeductionService::class)->deduct($branch, $product, 10);
})->throws(InsufficientStockException::class);

test('prevents overselling under concurrent deductions', function () {
    ['tenant' => $tenant, 'branch' => $branch] = createPharmacyContext();

    $product = seedProductWithBatches($tenant, $branch, [
        ['batch_no' => 'LOCK', 'expiry_date' => now()->addMonths(3), 'quantity' => 5],
    ]);

    $successes = 0;
    $failures = 0;

    foreach (range(1, 2) as $attempt) {
        try {
            DB::transaction(function () use ($branch, $product, &$successes): void {
                app(StockDeductionService::class)->deduct($branch, $product, 3);
                $successes++;
            });
        } catch (InsufficientStockException) {
            $failures++;
        }
    }

    expect($successes)->toBe(1)
        ->and($failures)->toBe(1)
        ->and(Stock::query()->sum('quantity'))->toBe(2);
});
