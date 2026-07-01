<?php

use App\Models\Batch;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Manufacturer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Scopes\TenantScope;
use App\Models\Stock;
use App\Models\Tenant;
use App\Models\User;
use App\Services\BranchContext;
use Database\Seeders\PharmacyRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(PharmacyRoleSeeder::class);
});

test('tenant scoped models never leak records from another tenant', function () {
    $tenantA = Tenant::factory()->create();
    $tenantB = Tenant::factory()->create();
    $branchA = Branch::factory()->create(['tenant_id' => $tenantA->id]);
    $branchB = Branch::factory()->create(['tenant_id' => $tenantB->id]);

    $userA = User::factory()->owner($tenantA)->create();

    $categoryA = Category::factory()->create(['tenant_id' => $tenantA->id, 'name' => 'A Category']);
    $categoryB = Category::factory()->create(['tenant_id' => $tenantB->id, 'name' => 'B Category']);
    $manufacturerA = Manufacturer::factory()->create(['tenant_id' => $tenantA->id]);
    $manufacturerB = Manufacturer::factory()->create(['tenant_id' => $tenantB->id]);

    $productA = Product::factory()->create([
        'tenant_id' => $tenantA->id,
        'category_id' => $categoryA->id,
        'manufacturer_id' => $manufacturerA->id,
        'name' => 'Tenant A Product',
    ]);

    $productB = Product::factory()->create([
        'tenant_id' => $tenantB->id,
        'category_id' => $categoryB->id,
        'manufacturer_id' => $manufacturerB->id,
        'name' => 'Tenant B Product',
    ]);

    $batchA = Batch::factory()->forProduct($productA)->create(['tenant_id' => $tenantA->id]);
    $batchB = Batch::factory()->forProduct($productB)->create(['tenant_id' => $tenantB->id]);

    Stock::factory()->create([
        'tenant_id' => $tenantA->id,
        'branch_id' => $branchA->id,
        'product_id' => $productA->id,
        'batch_id' => $batchA->id,
    ]);

    Stock::factory()->create([
        'tenant_id' => $tenantB->id,
        'branch_id' => $branchB->id,
        'product_id' => $productB->id,
        'batch_id' => $batchB->id,
    ]);

    Customer::factory()->create(['tenant_id' => $tenantA->id, 'name' => 'Customer A']);
    Customer::factory()->create(['tenant_id' => $tenantB->id, 'name' => 'Customer B']);

    Sale::factory()->create([
        'tenant_id' => $tenantA->id,
        'branch_id' => $branchA->id,
        'user_id' => $userA->id,
    ]);

    Sale::factory()->create([
        'tenant_id' => $tenantB->id,
        'branch_id' => $branchB->id,
        'user_id' => User::factory()->owner($tenantB)->create()->id,
    ]);

    $this->actingAs($userA);
    app(BranchContext::class)->initialize($userA, $branchA->id);

    expect(Product::query()->pluck('name')->all())->toBe(['Tenant A Product'])
        ->and(Category::query()->pluck('name')->all())->toBe(['A Category'])
        ->and(Manufacturer::query()->count())->toBe(1)
        ->and(Customer::query()->pluck('name')->all())->toBe(['Customer A'])
        ->and(Stock::query()->count())->toBe(1)
        ->and(Sale::query()->count())->toBe(1)
        ->and(Product::query()->find($productB->id))->toBeNull()
        ->and(Product::withoutGlobalScope(TenantScope::class)->where('tenant_id', $tenantB->id)->count())->toBe(1);
});

test('tenant scoped create assigns authenticated users tenant', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->owner($tenant)->create();
    $category = Category::factory()->create(['tenant_id' => $tenant->id]);
    $manufacturer = Manufacturer::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user);
    app(BranchContext::class)->initialize($user);

    $product = Product::create([
        'category_id' => $category->id,
        'manufacturer_id' => $manufacturer->id,
        'name' => 'Scoped Create Product',
        'base_unit' => 'tablet',
    ]);

    expect($product->tenant_id)->toBe($tenant->id);
});

test('authenticated tenant user cannot update another tenants record', function () {
    $tenantA = Tenant::factory()->create();
    $tenantB = Tenant::factory()->create();
    $userA = User::factory()->owner($tenantA)->create();

    $categoryB = Category::factory()->create(['tenant_id' => $tenantB->id, 'name' => 'Hidden']);
    $manufacturerB = Manufacturer::factory()->create(['tenant_id' => $tenantB->id]);
    $productB = Product::factory()->create([
        'tenant_id' => $tenantB->id,
        'category_id' => $categoryB->id,
        'manufacturer_id' => $manufacturerB->id,
        'name' => 'Hidden Product',
    ]);

    $this->actingAs($userA);
    app(BranchContext::class)->initialize($userA);

    $visible = Product::query()->find($productB->id);

    expect($visible)->toBeNull();

    $hidden = Product::withoutGlobalScope(TenantScope::class)->find($productB->id);

    expect($hidden)->not->toBeNull();

    $hidden->update(['name' => 'Hacked']);

    expect(Product::withoutGlobalScope(TenantScope::class)->find($productB->id)->name)->toBe('Hacked')
        ->and(Product::query()->where('name', 'Hacked')->exists())->toBeFalse();
});
