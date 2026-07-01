<?php

use App\Data\CartLine;
use App\Data\PaymentLine;
use App\Data\ReportFilters;
use App\Enums\PaymentMethod;
use App\Livewire\Reports\ProfitMarginReport;
use App\Livewire\Reports\ReportsDashboard;
use App\Livewire\Reports\SalesReport;
use App\Models\User;
use App\Services\CheckoutService;
use App\Services\Reports\ProfitMarginReportService;
use App\Services\Reports\SalesReportService;
use Database\Seeders\PharmacyRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(PharmacyRoleSeeder::class);
});

test('owner can access reports dashboard and sales report', function () {
    createPharmacyContext();

    $this->get(route('pharmacy.reports'))->assertOk();
    $this->get(route('pharmacy.reports.sales'))->assertOk();
    $this->get(route('pharmacy.reports.profit-margin'))->assertOk();
    $this->get(route('pharmacy.reports.inventory-valuation'))->assertOk();
    $this->get(route('pharmacy.reports.expiry'))->assertOk();
    $this->get(route('pharmacy.reports.tax'))->assertOk();
});

test('cashier cannot access reports', function () {
    ['tenant' => $tenant, 'branch' => $branch] = createPharmacyContext();
    $cashier = User::factory()->cashier($tenant, $branch)->create();
    $this->actingAs($cashier);

    $this->get(route('pharmacy.reports'))->assertForbidden();
});

test('reports dashboard shows today sales summary', function () {
    ['tenant' => $tenant, 'branch' => $branch, 'user' => $user] = createPharmacyContext();
    $product = seedCheckoutProduct($tenant, $branch);

    app(CheckoutService::class)->complete(
        branch: $branch,
        cashier: $user,
        lines: [new CartLine(productId: $product->id, productUnitId: null, quantity: 2)],
        payments: [new PaymentLine(PaymentMethod::Cash, '100.00')],
    );

    Livewire::test(ReportsDashboard::class)
        ->assertSee('Top Products Today')
        ->assertSee('1')
        ->assertSee($product->name);
});

test('sales report filters by date range and summarizes totals', function () {
    ['tenant' => $tenant, 'branch' => $branch, 'user' => $user] = createPharmacyContext();
    $product = seedCheckoutProduct($tenant, $branch);

    $sale = app(CheckoutService::class)->complete(
        branch: $branch,
        cashier: $user,
        lines: [new CartLine(productId: $product->id, productUnitId: null, quantity: 1)],
        payments: [new PaymentLine(PaymentMethod::Cash, '50.00')],
    );

    $from = now()->subDay()->toDateString();
    $to = now()->addDay()->toDateString();

    Livewire::test(SalesReport::class)
        ->set('from', $from)
        ->set('to', $to)
        ->assertSee($sale->invoice_no)
        ->assertSee('Sales Report');

    $summary = app(SalesReportService::class)->summary(
        ReportFilters::fromArray([
            'from' => $from,
            'to' => $to,
            'branch_id' => $branch->id,
        ])
    );

    expect($summary['sales_count'])->toBe(1)
        ->and((float) $summary['total'])->toBeGreaterThan(0);
});

test('profit margin report calculates cost and revenue from batch cost price', function () {
    ['tenant' => $tenant, 'branch' => $branch, 'user' => $user] = createPharmacyContext();
    $product = seedCheckoutProduct($tenant, $branch);

    app(CheckoutService::class)->complete(
        branch: $branch,
        cashier: $user,
        lines: [new CartLine(productId: $product->id, productUnitId: null, quantity: 1)],
        payments: [new PaymentLine(PaymentMethod::Cash, '50.00')],
    );

    $filters = ReportFilters::fromArray([
        'from' => now()->subDay()->toDateString(),
        'to' => now()->addDay()->toDateString(),
        'branch_id' => $branch->id,
    ]);

    $summary = app(ProfitMarginReportService::class)->summary($filters);

    expect((float) $summary['revenue'])->toBeGreaterThan(0)
        ->and((float) $summary['cost'])->toBeGreaterThan(0);

    Livewire::test(ProfitMarginReport::class)
        ->assertSee('Profit Margin Report')
        ->assertSee($product->name);
});

test('owner can export sales report as pdf and excel', function () {
    createPharmacyContext();

    $query = http_build_query([
        'type' => 'sales',
        'from' => today()->startOfMonth()->toDateString(),
        'to' => today()->toDateString(),
    ]);

    $this->get(route('pharmacy.reports.export.pdf').'?'.$query)
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');

    $this->get(route('pharmacy.reports.export.excel').'?'.$query)
        ->assertOk()
        ->assertDownload('sales-report-'.today()->startOfMonth()->toDateString().'.xlsx');
});
