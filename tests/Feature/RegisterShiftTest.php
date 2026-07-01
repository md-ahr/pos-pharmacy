<?php

use App\Data\CartLine;
use App\Data\PaymentLine;
use App\Enums\PaymentMethod;
use App\Enums\RegisterShiftStatus;
use App\Livewire\Settings\RegisterShiftManager;
use App\Models\RegisterShift;
use App\Services\CheckoutService;
use App\Services\RegisterShiftService;
use Database\Seeders\PharmacyRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(PharmacyRoleSeeder::class);
});

test('cannot open two shifts for the same branch', function () {
    ['branch' => $branch, 'user' => $user] = createPharmacyContext();
    $service = app(RegisterShiftService::class);

    $service->openShift($branch, $user, '75.00');
})->throws(InvalidArgumentException::class);

test('shift totals include cash sales during open shift', function () {
    ['tenant' => $tenant, 'branch' => $branch, 'user' => $user] = createPharmacyContext();
    $product = seedCheckoutProduct($tenant, $branch);
    $service = app(RegisterShiftService::class);

    $shift = $service->openShiftForBranch($branch);

    expect($shift)->not->toBeNull();

    app(CheckoutService::class)->complete(
        branch: $branch,
        cashier: $user,
        lines: [new CartLine(productId: $product->id, productUnitId: null, quantity: 1)],
        payments: [new PaymentLine(PaymentMethod::Cash, '50.00')],
    );

    $totals = $service->calculateShiftTotals($shift->fresh());

    expect((float) $totals['cash_sales'])->toBeGreaterThan(0);

    $closed = $service->closeShift($shift->fresh(), $user, bcadd('100.00', $totals['cash_sales'], 2));

    expect($closed->status)->toBe(RegisterShiftStatus::Closed)
        ->and((float) $closed->sales_total)->toBeGreaterThan(0);
});

test('register shift manager page loads for owner', function () {
    ['user' => $user, 'branch' => $branch] = createPharmacyContext();

    $this->actingAs($user)
        ->get(route('pharmacy.settings.register'))
        ->assertOk();

    Livewire::actingAs($user)
        ->test(RegisterShiftManager::class)
        ->assertSee('Open');

    expect(RegisterShift::query()->where('status', RegisterShiftStatus::Open)->count())->toBe(1);
});
