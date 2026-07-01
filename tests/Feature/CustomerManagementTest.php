<?php

use App\Data\CartLine;
use App\Data\PaymentLine;
use App\Enums\PaymentMethod;
use App\Enums\SaleStatus;
use App\Livewire\Customers\CustomerForm;
use App\Livewire\Customers\CustomerShow;
use App\Livewire\Customers\CustomersIndex;
use App\Livewire\Pos\SaleScreen;
use App\Models\Customer;
use App\Models\Tenant;
use App\Services\CheckoutService;
use Database\Seeders\PharmacyRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(PharmacyRoleSeeder::class);
});

test('pos user can access customer screens', function () {
    createPharmacyContext();

    $this->get(route('pharmacy.customers'))->assertOk();
    $this->get(route('pharmacy.customers.create'))->assertOk();
});

test('customer form creates a customer record', function () {
    createPharmacyContext();

    Livewire::test(CustomerForm::class)
        ->set('name', 'Jane Doe')
        ->set('phone', '555-0100')
        ->set('email', 'jane@example.com')
        ->call('save')
        ->assertRedirect();

    $customer = Customer::query()->where('phone', '555-0100')->first();

    expect($customer)->not->toBeNull()
        ->and($customer->name)->toBe('Jane Doe')
        ->and($customer->email)->toBe('jane@example.com');
});

test('customer form requires at least one identifier', function () {
    createPharmacyContext();

    Livewire::test(CustomerForm::class)
        ->set('name', '')
        ->set('phone', '')
        ->set('email', '')
        ->call('save')
        ->assertHasErrors(['name'])
        ->assertSee('Provide at least a name, phone, or email.')
        ->assertSeeHtml('class="form-error"');
});

test('customer show lists purchase history', function () {
    ['tenant' => $tenant, 'branch' => $branch, 'user' => $user] = createPharmacyContext();
    $customer = Customer::factory()->create([
        'tenant_id' => $tenant->id,
        'name' => 'History Customer',
        'phone' => '555-0200',
    ]);

    $product = seedCheckoutProduct($tenant, $branch);

    $sale = app(CheckoutService::class)->complete(
        branch: $branch,
        cashier: $user,
        lines: [new CartLine(productId: $product->id, productUnitId: null, quantity: 1)],
        payments: [new PaymentLine(PaymentMethod::Cash, '50.00')],
        customerId: $customer->id,
    );

    Livewire::test(CustomerShow::class, ['customer' => $customer])
        ->assertSee('History Customer')
        ->assertSee($sale->invoice_no)
        ->assertSee('Purchase History');
});

test('checkout attaches customer to completed sale', function () {
    ['tenant' => $tenant, 'branch' => $branch, 'user' => $user] = createPharmacyContext();
    $customer = Customer::factory()->create([
        'tenant_id' => $tenant->id,
        'name' => 'POS Customer',
    ]);
    $product = seedCheckoutProduct($tenant, $branch);

    $sale = app(CheckoutService::class)->complete(
        branch: $branch,
        cashier: $user,
        lines: [new CartLine(productId: $product->id, productUnitId: null, quantity: 1)],
        payments: [new PaymentLine(PaymentMethod::Cash, '50.00')],
        customerId: $customer->id,
    );

    expect($sale->customer_id)->toBe($customer->id)
        ->and($sale->status)->toBe(SaleStatus::Completed);
});

test('anonymous checkout leaves customer null', function () {
    ['tenant' => $tenant, 'branch' => $branch, 'user' => $user] = createPharmacyContext();
    $product = seedCheckoutProduct($tenant, $branch);

    $sale = app(CheckoutService::class)->complete(
        branch: $branch,
        cashier: $user,
        lines: [new CartLine(productId: $product->id, productUnitId: null, quantity: 1)],
        payments: [new PaymentLine(PaymentMethod::Cash, '50.00')],
    );

    expect($sale->customer_id)->toBeNull();
});

test('pos screen can select and clear a customer', function () {
    ['tenant' => $tenant] = createPharmacyContext();
    $customer = Customer::factory()->create([
        'tenant_id' => $tenant->id,
        'name' => 'Selectable Customer',
        'phone' => '555-0300',
    ]);

    Livewire::test(SaleScreen::class)
        ->call('selectCustomer', $customer->id)
        ->assertSet('customerId', $customer->id)
        ->call('clearCustomer')
        ->assertSet('customerId', null);
});

test('customers index is tenant scoped', function () {
    ['tenant' => $tenantA] = createPharmacyContext();
    $tenantB = Tenant::factory()->create();

    Customer::factory()->create(['tenant_id' => $tenantA->id, 'name' => 'Tenant A Customer']);
    Customer::factory()->create(['tenant_id' => $tenantB->id, 'name' => 'Tenant B Customer']);

    Livewire::test(CustomersIndex::class)
        ->assertSee('Tenant A Customer')
        ->assertDontSee('Tenant B Customer');
});

test('held sale preserves customer attachment', function () {
    ['tenant' => $tenant, 'branch' => $branch, 'user' => $user] = createPharmacyContext();
    $customer = Customer::factory()->create([
        'tenant_id' => $tenant->id,
        'name' => 'Held Customer',
    ]);
    $product = seedCheckoutProduct($tenant, $branch);

    $held = app(CheckoutService::class)->hold(
        branch: $branch,
        cashier: $user,
        lines: [new CartLine(productId: $product->id, productUnitId: null, quantity: 1)],
        customerId: $customer->id,
    );

    expect($held->customer_id)->toBe($customer->id)
        ->and($held->status)->toBe(SaleStatus::Held);
});
