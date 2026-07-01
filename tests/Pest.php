<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
 // ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

use App\Models\Batch;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Manufacturer;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Tenant;
use App\Models\User;
use App\Services\BranchContext;

function createPharmacyContext(?Tenant $tenant = null): array
{
    $tenant ??= Tenant::factory()->create();
    $branch = Branch::factory()->main()->create(['tenant_id' => $tenant->id]);
    $user = User::factory()->owner($tenant)->create();

    test()->actingAs($user);
    app(BranchContext::class)->initialize($user, $branch->id);

    return compact('tenant', 'branch', 'user');
}

function seedCheckoutProduct(Tenant $tenant, Branch $branch, array $overrides = []): Product
{
    $category = Category::factory()->create(['tenant_id' => $tenant->id]);
    $manufacturer = Manufacturer::factory()->create(['tenant_id' => $tenant->id]);

    $product = Product::factory()->create(array_merge([
        'tenant_id' => $tenant->id,
        'category_id' => $category->id,
        'manufacturer_id' => $manufacturer->id,
        'name' => 'Checkout Medicine',
        'base_unit' => 'tablet',
    ], $overrides));

    $batch = Batch::factory()->forProduct($product)->create([
        'tenant_id' => $tenant->id,
        'batch_no' => 'B-001',
        'expiry_date' => now()->addMonths(6),
        'selling_price' => '10.00',
    ]);

    Stock::factory()->create([
        'tenant_id' => $tenant->id,
        'branch_id' => $branch->id,
        'product_id' => $product->id,
        'batch_id' => $batch->id,
        'quantity' => 100,
    ]);

    return $product->fresh(['units', 'batches']);
}
