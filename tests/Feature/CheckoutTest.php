<?php

use App\Data\CartLine;
use App\Data\PaymentLine;
use App\Enums\PaymentMethod;
use App\Enums\SaleStatus;
use App\Models\Batch;
use App\Models\ProductUnit;
use App\Models\Stock;
use App\Models\User;
use App\Services\CheckoutService;
use App\Services\SaleRefundService;
use Database\Seeders\PharmacyRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(PharmacyRoleSeeder::class);
});

test('completes a sale and deducts stock', function () {
    ['tenant' => $tenant, 'branch' => $branch, 'user' => $user] = createPharmacyContext();
    $product = seedCheckoutProduct($tenant, $branch);

    $sale = app(CheckoutService::class)->complete(
        branch: $branch,
        cashier: $user,
        lines: [
            new CartLine(
                productId: $product->id,
                productUnitId: null,
                quantity: 2,
            ),
        ],
        payments: [
            new PaymentLine(PaymentMethod::Cash, '23.00'),
        ],
    );

    expect($sale->status)->toBe(SaleStatus::Completed)
        ->and($sale->invoice_no)->toStartWith('INV-')
        ->and($sale->items)->toHaveCount(1)
        ->and((float) $sale->total)->toBeGreaterThan(0)
        ->and(Stock::query()->sum('quantity'))->toBe(98);
});

test('holds a sale without deducting stock and can complete later', function () {
    ['tenant' => $tenant, 'branch' => $branch, 'user' => $user] = createPharmacyContext();
    $product = seedCheckoutProduct($tenant, $branch);

    $held = app(CheckoutService::class)->hold(
        branch: $branch,
        cashier: $user,
        lines: [new CartLine(productId: $product->id, productUnitId: null, quantity: 3)],
    );

    expect($held->status)->toBe(SaleStatus::Held)
        ->and(Stock::query()->sum('quantity'))->toBe(100);

    $completed = app(CheckoutService::class)->complete(
        branch: $branch,
        cashier: $user,
        lines: [new CartLine(productId: $product->id, productUnitId: null, quantity: 3)],
        payments: [new PaymentLine(PaymentMethod::Cash, (string) $held->total)],
        heldSale: $held,
    );

    expect($completed->id)->toBe($held->id)
        ->and($completed->status)->toBe(SaleStatus::Completed)
        ->and(Stock::query()->sum('quantity'))->toBe(97);
});

test('converts sell units before deducting stock', function () {
    ['tenant' => $tenant, 'branch' => $branch, 'user' => $user] = createPharmacyContext();
    $product = seedCheckoutProduct($tenant, $branch);

    $strip = ProductUnit::factory()->create([
        'product_id' => $product->id,
        'unit_name' => 'strip',
        'conversion_factor' => 10,
        'selling_price' => '90.00',
        'is_default' => true,
    ]);

    $sale = app(CheckoutService::class)->complete(
        branch: $branch,
        cashier: $user,
        lines: [new CartLine(productId: $product->id, productUnitId: $strip->id, quantity: 2)],
        payments: [new PaymentLine(PaymentMethod::Card, '500.00')],
    );

    expect($sale->items->first()->quantity_base)->toBe(20)
        ->and(Stock::query()->sum('quantity'))->toBe(80);
});

test('blocks checkout when stock is expired', function () {
    ['tenant' => $tenant, 'branch' => $branch, 'user' => $user] = createPharmacyContext();
    $product = seedCheckoutProduct($tenant, $branch, ['name' => 'Expired Product']);

    Batch::query()->update(['expiry_date' => now()->subDay()]);

    app(CheckoutService::class)->complete(
        branch: $branch,
        cashier: $user,
        lines: [new CartLine(productId: $product->id, productUnitId: null, quantity: 1)],
        payments: [new PaymentLine(PaymentMethod::Cash, '20.00')],
    );
})->throws(InvalidArgumentException::class, 'No available batch');

test('refunds a completed sale and restores stock', function () {
    ['tenant' => $tenant, 'branch' => $branch, 'user' => $user] = createPharmacyContext();
    $product = seedCheckoutProduct($tenant, $branch);

    $sale = app(CheckoutService::class)->complete(
        branch: $branch,
        cashier: $user,
        lines: [new CartLine(productId: $product->id, productUnitId: null, quantity: 4)],
        payments: [new PaymentLine(PaymentMethod::Cash, '100.00')],
    );

    expect(Stock::query()->sum('quantity'))->toBe(96);

    $refunded = app(SaleRefundService::class)->refund($sale, $branch);

    expect($refunded->status)->toBe(SaleStatus::Refunded)
        ->and(Stock::query()->sum('quantity'))->toBe(100);
});

test('pos screen requires pos privilege', function () {
    ['tenant' => $tenant, 'branch' => $branch] = createPharmacyContext();
    $cashier = User::factory()->cashier($tenant, $branch)->create();

    $this->actingAs($cashier)
        ->get(route('pharmacy.pos'))
        ->assertOk();
});

test('receipt is available for completed sales in the same tenant', function () {
    ['tenant' => $tenant, 'branch' => $branch, 'user' => $user] = createPharmacyContext();
    $product = seedCheckoutProduct($tenant, $branch);

    $sale = app(CheckoutService::class)->complete(
        branch: $branch,
        cashier: $user,
        lines: [new CartLine(productId: $product->id, productUnitId: null, quantity: 1)],
        payments: [new PaymentLine(PaymentMethod::Cash, '20.00')],
    );

    $this->actingAs($user)
        ->get(route('pharmacy.pos.receipt', $sale))
        ->assertOk()
        ->assertSee($sale->invoice_no)
        ->assertSee($product->name);
});
