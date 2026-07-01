<?php

use App\Models\Batch;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Manufacturer;
use App\Models\Product;
use App\Models\Scopes\TenantScope;
use App\Models\Stock;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\PharmacySeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

test('all phase 2 migrations run successfully', function () {
    expect(Schema::hasTable('tenants'))->toBeTrue()
        ->and(Schema::hasTable('branches'))->toBeTrue()
        ->and(Schema::hasTable('categories'))->toBeTrue()
        ->and(Schema::hasTable('manufacturers'))->toBeTrue()
        ->and(Schema::hasTable('products'))->toBeTrue()
        ->and(Schema::hasTable('product_units'))->toBeTrue()
        ->and(Schema::hasTable('batches'))->toBeTrue()
        ->and(Schema::hasTable('stock'))->toBeTrue()
        ->and(Schema::hasTable('customers'))->toBeTrue()
        ->and(Schema::hasTable('sales'))->toBeTrue()
        ->and(Schema::hasTable('sale_items'))->toBeTrue()
        ->and(Schema::hasTable('sale_payments'))->toBeTrue()
        ->and(Schema::hasTable('suppliers'))->toBeTrue()
        ->and(Schema::hasTable('purchase_orders'))->toBeTrue()
        ->and(Schema::hasTable('purchase_order_items'))->toBeTrue()
        ->and(Schema::hasTable('stock_adjustments'))->toBeTrue()
        ->and(Schema::hasTable('stock_transfers'))->toBeTrue()
        ->and(Schema::hasTable('audit_logs'))->toBeTrue();
});

test('users table has pharmacy columns', function () {
    expect(Schema::hasColumns('users', ['tenant_id', 'branch_id', 'role', 'is_active']))->toBeTrue();
});

test('tenant scope filters products to authenticated user tenant', function () {
    $tenantA = Tenant::factory()->create();
    $tenantB = Tenant::factory()->create();

    $userA = User::factory()->forTenant($tenantA)->create();
    $categoryA = Category::factory()->create(['tenant_id' => $tenantA->id]);
    $manufacturerA = Manufacturer::factory()->create(['tenant_id' => $tenantA->id]);
    $categoryB = Category::factory()->create(['tenant_id' => $tenantB->id]);
    $manufacturerB = Manufacturer::factory()->create(['tenant_id' => $tenantB->id]);

    Product::factory()->create([
        'tenant_id' => $tenantA->id,
        'category_id' => $categoryA->id,
        'manufacturer_id' => $manufacturerA->id,
        'name' => 'Tenant A Product',
    ]);

    Product::factory()->create([
        'tenant_id' => $tenantB->id,
        'category_id' => $categoryB->id,
        'manufacturer_id' => $manufacturerB->id,
        'name' => 'Tenant B Product',
    ]);

    $this->actingAs($userA);

    $visibleProducts = Product::all();

    expect($visibleProducts)->toHaveCount(1)
        ->and($visibleProducts->first()->name)->toBe('Tenant A Product');
});

test('belongs to tenant auto sets tenant_id on create', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->forTenant($tenant)->create();
    $category = Category::factory()->create(['tenant_id' => $tenant->id]);
    $manufacturer = Manufacturer::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user);

    $product = Product::create([
        'category_id' => $category->id,
        'manufacturer_id' => $manufacturer->id,
        'name' => 'Auto-scoped Product',
        'base_unit' => 'tablet',
    ]);

    expect($product->tenant_id)->toBe($tenant->id);
});

test('pharmacy seeder creates sample tenant with products and stock', function () {
    $this->seed(PharmacySeeder::class);

    $tenant = Tenant::where('slug', 'demo-pharmacy')->first();

    expect($tenant)->not->toBeNull()
        ->and($tenant->branches()->count())->toBe(1)
        ->and($tenant->users()->where('role', 'owner')->count())->toBe(1)
        ->and(Product::withoutGlobalScope(TenantScope::class)->where('tenant_id', $tenant->id)->count())->toBe(3)
        ->and(Stock::withoutGlobalScope(TenantScope::class)->where('tenant_id', $tenant->id)->count())->toBe(3);
});

test('stock quantity cannot go negative on postgresql', function () {
    if (config('database.default') !== 'pgsql' && DB::connection()->getDriverName() !== 'pgsql') {
        $this->markTestSkipped('CHECK constraint is enforced on PostgreSQL only.');
    }

    $tenant = Tenant::factory()->create();
    $branch = Branch::factory()->create(['tenant_id' => $tenant->id]);
    $category = Category::factory()->create(['tenant_id' => $tenant->id]);
    $manufacturer = Manufacturer::factory()->create(['tenant_id' => $tenant->id]);
    $product = Product::factory()->create([
        'tenant_id' => $tenant->id,
        'category_id' => $category->id,
        'manufacturer_id' => $manufacturer->id,
    ]);
    $batch = Batch::factory()->forProduct($product)->create();

    $stock = Stock::factory()->create([
        'tenant_id' => $tenant->id,
        'branch_id' => $branch->id,
        'product_id' => $product->id,
        'batch_id' => $batch->id,
        'quantity' => 10,
    ]);

    expect(fn () => $stock->update(['quantity' => -1]))->toThrow(QueryException::class);
});
