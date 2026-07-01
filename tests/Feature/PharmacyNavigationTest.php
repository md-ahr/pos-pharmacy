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
        ->assertDontSee('Settings');
});

test('pharmacy menu routes are registered', function () {
    expect(Route::has('pharmacy.pos'))->toBeTrue()
        ->and(Route::has('pharmacy.inventory'))->toBeTrue()
        ->and(Route::has('pharmacy.reports'))->toBeTrue()
        ->and(Route::has('pharmacy.settings.general'))->toBeTrue()
        ->and(Route::has('pharmacy.pos.receipt.pdf'))->toBeTrue();
});
