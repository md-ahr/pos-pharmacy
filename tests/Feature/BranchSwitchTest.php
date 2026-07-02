<?php

use App\Livewire\Dashboard\BranchSwitcher;
use App\Livewire\Inventory\PurchaseOrdersIndex;
use App\Livewire\Pos\SaleScreen;
use App\Livewire\Reports\InventoryValuationReport;
use App\Livewire\Reports\ReportsDashboard;
use App\Livewire\Reports\SalesReport;
use App\Models\Branch;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Services\BranchContext;
use Database\Seeders\PharmacyRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(PharmacyRoleSeeder::class);
});

test('branch switcher updates session and dispatches event', function () {
    ['tenant' => $tenant, 'user' => $user, 'branch' => $mainBranch] = createPharmacyContext();
    $secondBranch = Branch::factory()->create([
        'tenant_id' => $tenant->id,
        'name' => 'Downtown Branch',
    ]);

    Livewire::test(BranchSwitcher::class)
        ->set('selectedBranchId', $secondBranch->id)
        ->assertDispatched('branch-switched');

    expect(app(BranchContext::class)->activeBranchId())->toBe($secondBranch->id);
});

test('sales report syncs branch filter when branch is switched', function () {
    ['tenant' => $tenant, 'user' => $user, 'branch' => $mainBranch] = createPharmacyContext();
    $secondBranch = Branch::factory()->create(['tenant_id' => $tenant->id]);

    $component = Livewire::test(SalesReport::class);
    expect($component->get('branchId'))->toBe((string) $mainBranch->id);

    app(BranchContext::class)->switchBranch($user, $secondBranch->id);
    $component->dispatch('branch-switched');

    expect($component->get('branchId'))->toBe((string) $secondBranch->id);
});

test('inventory valuation report syncs branch filter when branch is switched', function () {
    ['tenant' => $tenant, 'user' => $user, 'branch' => $mainBranch] = createPharmacyContext();
    $secondBranch = Branch::factory()->create(['tenant_id' => $tenant->id]);

    $component = Livewire::test(InventoryValuationReport::class);
    expect($component->get('branchId'))->toBe((string) $mainBranch->id);

    app(BranchContext::class)->switchBranch($user, $secondBranch->id);
    $component->dispatch('branch-switched');

    expect($component->get('branchId'))->toBe((string) $secondBranch->id);
});

test('pos clears cart when branch is switched', function () {
    ['tenant' => $tenant, 'branch' => $branch] = createPharmacyContext();
    $product = seedCheckoutProduct($tenant, $branch);

    Livewire::test(SaleScreen::class)
        ->call('addProduct', $product->id)
        ->assertSet('cart', fn (array $cart): bool => count($cart) === 1)
        ->dispatch('branch-switched')
        ->assertSet('cart', []);
});

test('purchase orders index only shows active branch orders', function () {
    ['tenant' => $tenant, 'user' => $user, 'branch' => $mainBranch] = createPharmacyContext();
    $secondBranch = Branch::factory()->create([
        'tenant_id' => $tenant->id,
        'name' => 'Downtown Branch',
    ]);
    $supplier = Supplier::factory()->create(['tenant_id' => $tenant->id]);

    $mainOrder = PurchaseOrder::factory()->create([
        'tenant_id' => $tenant->id,
        'branch_id' => $mainBranch->id,
        'supplier_id' => $supplier->id,
        'created_by' => $user->id,
        'reference_no' => 'PO-MAIN-001',
    ]);

    PurchaseOrder::factory()->create([
        'tenant_id' => $tenant->id,
        'branch_id' => $secondBranch->id,
        'supplier_id' => $supplier->id,
        'created_by' => $user->id,
        'reference_no' => 'PO-DT-001',
    ]);

    Livewire::test(PurchaseOrdersIndex::class)
        ->assertSee($mainOrder->reference_no)
        ->assertDontSee('PO-DT-001');

    app(BranchContext::class)->switchBranch($user, $secondBranch->id);

    Livewire::test(PurchaseOrdersIndex::class)
        ->assertSee('PO-DT-001')
        ->assertDontSee($mainOrder->reference_no);
});

test('reports dashboard re-renders with switched branch context', function () {
    ['tenant' => $tenant, 'user' => $user, 'branch' => $mainBranch] = createPharmacyContext();
    $secondBranch = Branch::factory()->create([
        'tenant_id' => $tenant->id,
        'name' => 'Downtown Branch',
    ]);

    $component = Livewire::test(ReportsDashboard::class);
    $component->assertSee($mainBranch->name);

    app(BranchContext::class)->switchBranch($user, $secondBranch->id);
    $component->dispatch('branch-switched');

    $component->assertSee('Downtown Branch');
});
