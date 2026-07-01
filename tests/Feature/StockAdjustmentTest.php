<?php

use App\Enums\StockAdjustmentReason;
use App\Models\AuditLog;
use App\Models\Batch;
use App\Models\Stock;
use App\Services\StockAdjustmentService;
use Database\Seeders\PharmacyRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(PharmacyRoleSeeder::class);
});

test('stock adjustment updates quantity and writes audit log', function () {
    ['tenant' => $tenant, 'branch' => $branch, 'user' => $user] = createPharmacyContext();
    $product = seedCheckoutProduct($tenant, $branch);
    $batch = Batch::query()->where('product_id', $product->id)->firstOrFail();

    $adjustment = app(StockAdjustmentService::class)->adjust(
        branch: $branch,
        product: $product,
        batch: $batch,
        quantityDelta: -5,
        reason: StockAdjustmentReason::Damage,
        adjustedBy: $user,
        notes: 'Broken packaging',
    );

    expect($adjustment->quantity_delta)->toBe(-5)
        ->and(Stock::query()->sum('quantity'))->toBe(95)
        ->and(AuditLog::query()->where('action', 'stock.adjusted')->where('auditable_id', $adjustment->id)->exists())->toBeTrue();
});
