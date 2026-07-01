<?php

use App\Data\CartLine;
use App\Data\PaymentLine;
use App\Enums\PaymentMethod;
use App\Enums\PharmacyRole;
use App\Enums\RegisterShiftStatus;
use App\Livewire\Settings\BranchForm;
use App\Livewire\Settings\StaffForm;
use App\Livewire\Settings\TenantSettingsForm;
use App\Models\Branch;
use App\Models\Tenant;
use App\Models\TenantSetting;
use App\Models\User;
use App\Services\CheckoutService;
use App\Services\RegisterShiftService;
use App\Services\TenantSettingsService;
use Database\Seeders\PharmacyRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(PharmacyRoleSeeder::class);
});

test('owner can view and update tenant settings', function () {
    ['tenant' => $tenant, 'user' => $user] = createPharmacyContext();

    $this->actingAs($user)
        ->get(route('pharmacy.settings.general'))
        ->assertOk();

    Livewire::actingAs($user)
        ->test(TenantSettingsForm::class)
        ->set('currencyCode', 'SAR')
        ->set('taxRate', '0.0500')
        ->set('receiptHeader', 'Welcome')
        ->set('receiptFooter', 'See you soon')
        ->call('save')
        ->assertHasNoErrors();

    $settings = app(TenantSettingsService::class)->forTenant($tenant);

    expect($settings->currency_code)->toBe('SAR')
        ->and((string) $settings->tax_rate)->toBe('0.0500')
        ->and($settings->receipt_header)->toBe('Welcome')
        ->and($settings->receipt_footer)->toBe('See you soon');
});

test('owner can create and edit branches', function () {
    ['user' => $user] = createPharmacyContext();

    Livewire::actingAs($user)
        ->test(BranchForm::class)
        ->set('name', 'Downtown')
        ->set('code', 'DT01')
        ->set('phone', '555-0100')
        ->set('isActive', true)
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('pharmacy.settings.branches'));

    $branch = Branch::query()->where('code', 'DT01')->first();

    expect($branch)->not->toBeNull()
        ->and($branch->name)->toBe('Downtown');
});

test('owner can create staff with role and branch assignment', function () {
    ['tenant' => $tenant, 'branch' => $branch, 'user' => $user] = createPharmacyContext();

    Livewire::actingAs($user)
        ->test(StaffForm::class)
        ->set('name', 'Jane Cashier')
        ->set('email', 'cashier@example.test')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->set('role', PharmacyRole::Cashier->value)
        ->set('branchId', $branch->id)
        ->call('save')
        ->assertHasNoErrors();

    $staff = User::query()->where('email', 'cashier@example.test')->first();

    expect($staff)->not->toBeNull()
        ->and($staff->tenant_id)->toBe($tenant->id)
        ->and($staff->branch_id)->toBe($branch->id)
        ->and($staff->role)->toBe(PharmacyRole::Cashier->value);
});

test('settings routes are forbidden without settings privilege', function () {
    ['tenant' => $tenant, 'branch' => $branch] = createPharmacyContext();
    $cashier = User::factory()->cashier($tenant, $branch)->create();

    $this->actingAs($cashier)
        ->get(route('pharmacy.settings.general'))
        ->assertForbidden();
});

test('checkout uses tenant tax rate from settings', function () {
    ['tenant' => $tenant, 'branch' => $branch, 'user' => $user] = createPharmacyContext();
    $product = seedCheckoutProduct($tenant, $branch);

    app(TenantSettingsService::class)->update($tenant, [
        'tax_rate' => '0.0500',
    ]);

    $sale = app(CheckoutService::class)->complete(
        branch: $branch,
        cashier: $user,
        lines: [new CartLine(productId: $product->id, productUnitId: null, quantity: 1)],
        payments: [new PaymentLine(PaymentMethod::Cash, '50.00')],
    );

    expect((float) $sale->tax_amount)->toBeGreaterThan(0);
});

test('register shift can be opened and closed with reconciliation', function () {
    ['tenant' => $tenant, 'branch' => $branch, 'user' => $user] = createPharmacyContext();
    $service = app(RegisterShiftService::class);

    $shift = $service->openShift($branch, $user, '100.00');

    expect($shift->status)->toBe(RegisterShiftStatus::Open)
        ->and((string) $shift->opening_float)->toBe('100.00');

    $closed = $service->closeShift($shift, $user, '100.00', 'Balanced');

    expect($closed->status)->toBe(RegisterShiftStatus::Closed)
        ->and((string) $closed->cash_variance)->toBe('0.00')
        ->and($closed->notes)->toBe('Balanced');
});

test('tenant settings are isolated between tenants', function () {
    ['tenant' => $tenantA, 'user' => $userA] = createPharmacyContext();
    $tenantB = Tenant::factory()->create();
    TenantSetting::factory()->forTenant($tenantB)->create([
        'receipt_header' => 'Tenant B Header',
    ]);

    app(TenantSettingsService::class)->update($tenantA, [
        'receipt_header' => 'Tenant A Header',
    ]);

    $this->actingAs($userA);

    expect(TenantSetting::query()->value('receipt_header'))->toBe('Tenant A Header');
});
