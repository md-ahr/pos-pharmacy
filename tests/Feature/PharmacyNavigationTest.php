<?php

use App\Models\User;
use App\Services\BranchContext;
use Database\Seeders\PharmacyRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(PharmacyRoleSeeder::class);
});

test('pharmacy menu items appear in dashboard for owner', function () {
    ['user' => $user] = createPharmacyContext();

    $this->actingAs($user)
        ->get(route('tyro-dashboard.index'))
        ->assertOk()
        ->assertSee('Point of Sale')
        ->assertSee('Inventory')
        ->assertSee('Reports')
        ->assertSee('Settings');
});

test('cashier sees pos menu but not settings', function () {
    ['tenant' => $tenant, 'branch' => $branch] = createPharmacyContext();
    $cashier = User::factory()->cashier($tenant, $branch)->create();

    app(BranchContext::class)->initialize($cashier, $branch->id);

    $this->actingAs($cashier)
        ->get(route('tyro-dashboard.index'))
        ->assertOk()
        ->assertSee('Point of Sale')
        ->assertDontSee(route('pharmacy.settings.general'), false);
});

test('dashboard layout initializes theme before paint', function () {
    ['user' => $user] = createPharmacyContext();

    $response = $this->actingAs($user)
        ->get(route('pharmacy.inventory.products.create'));

    $response
        ->assertOk()
        ->assertSee('tyro-dashboard-theme', false)
        ->assertSee('.btn-ghost,', false)
        ->assertDontSee('<html lang="en" class="light">', false);
});

test('pharmacy menu routes are registered', function () {
    expect(Route::has('pharmacy.pos'))->toBeTrue()
        ->and(Route::has('pharmacy.inventory'))->toBeTrue()
        ->and(Route::has('pharmacy.inventory.categories'))->toBeTrue()
        ->and(Route::has('pharmacy.inventory.manufacturers'))->toBeTrue()
        ->and(Route::has('pharmacy.reports'))->toBeTrue()
        ->and(Route::has('pharmacy.settings.general'))->toBeTrue()
        ->and(Route::has('pharmacy.pos.receipt.pdf'))->toBeTrue();
});

test('parent sidebar menu stays active on inventory subpages', function () {
    ['user' => $user] = createPharmacyContext();

    $this->actingAs($user)
        ->get(route('pharmacy.inventory.products'))
        ->assertOk()
        ->assertSee('href="'.route('pharmacy.inventory').'" class="sidebar-link active"', false);
});

test('parent sidebar menu stays active on reports subpages', function () {
    ['user' => $user] = createPharmacyContext();

    $this->actingAs($user)
        ->get(route('pharmacy.reports.sales'))
        ->assertOk()
        ->assertSee('href="'.route('pharmacy.reports').'" class="sidebar-link active"', false);
});

test('parent sidebar menu stays active on settings subpages', function () {
    ['user' => $user] = createPharmacyContext();

    $this->actingAs($user)
        ->get(route('pharmacy.settings.branches'))
        ->assertOk()
        ->assertSee('href="'.route('pharmacy.settings.general').'" class="sidebar-link active"', false);
});

test('inventory sub nav highlights active tab', function () {
    ['user' => $user] = createPharmacyContext();

    $this->actingAs($user)
        ->get(route('pharmacy.inventory.products'))
        ->assertOk()
        ->assertSee('href="'.route('pharmacy.inventory.products').'" class="btn btn-sm btn-primary"', false)
        ->assertSee('href="'.route('pharmacy.inventory').'" class="btn btn-sm btn-ghost"', false);
});
