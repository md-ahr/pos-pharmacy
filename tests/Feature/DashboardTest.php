<?php

use App\Data\CartLine;
use App\Data\PaymentLine;
use App\Enums\PaymentMethod;
use App\Livewire\Dashboard\Welcome;
use App\Models\User;
use App\Services\BranchContext;
use App\Services\CheckoutService;
use Database\Seeders\PharmacyRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(PharmacyRoleSeeder::class);
});

test('dashboard shows pharmacy metrics for owner', function () {
    ['tenant' => $tenant, 'branch' => $branch, 'user' => $user] = createPharmacyContext();
    $product = seedCheckoutProduct($tenant, $branch);

    app(CheckoutService::class)->complete(
        branch: $branch,
        cashier: $user,
        lines: [new CartLine(productId: $product->id, productUnitId: null, quantity: 2)],
        payments: [new PaymentLine(PaymentMethod::Cash, '100.00')],
    );

    $this->actingAs($user)
        ->get(route('tyro-dashboard.index'))
        ->assertOk()
        ->assertSee("Welcome back, {$user->name}")
        ->assertSee($branch->name)
        ->assertSee('Top Products Today')
        ->assertSee('Total Revenue')
        ->assertSee('Total Sales')
        ->assertSee($product->name)
        ->assertSee('Open POS');
});

test('cashier dashboard shows pos quick action but not settings', function () {
    ['tenant' => $tenant, 'branch' => $branch] = createPharmacyContext();
    $cashier = User::factory()->cashier($tenant, $branch)->create();

    app(BranchContext::class)->initialize($cashier, $branch->id);

    $this->actingAs($cashier)
        ->get(route('tyro-dashboard.index'))
        ->assertOk()
        ->assertSee('Open POS')
        ->assertDontSee('href="'.route('pharmacy.settings.general').'"', false);
});

test('welcome livewire component renders low stock and expiry widgets', function () {
    createPharmacyContext();

    Livewire::test(Welcome::class)
        ->assertSee('Low Stock')
        ->assertSee('Expiring Within 90 Days')
        ->assertSee('Quick Actions');
});
