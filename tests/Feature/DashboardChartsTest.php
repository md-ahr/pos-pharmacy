<?php

use App\Data\CartLine;
use App\Data\PaymentLine;
use App\Enums\PaymentMethod;
use App\Livewire\Dashboard\Welcome;
use App\Services\CheckoutService;
use App\Services\Reports\DashboardMetricsService;
use App\Support\SvgChartBuilder;
use Database\Seeders\PharmacyRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(PharmacyRoleSeeder::class);
});

test('dashboard shows revenue and sales charts', function () {
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
        ->assertSee('Revenue (Last 14 Days)')
        ->assertSee('Sales (Last 7 Days)')
        ->assertSee('Payment Mix (Last 7 Days)')
        ->assertSee('Top Products Today')
        ->assertSee($product->name);
});

test('dashboard metrics service builds chart data from sales', function () {
    ['tenant' => $tenant, 'branch' => $branch, 'user' => $user] = createPharmacyContext();
    $product = seedCheckoutProduct($tenant, $branch);

    app(CheckoutService::class)->complete(
        branch: $branch,
        cashier: $user,
        lines: [new CartLine(productId: $product->id, productUnitId: null, quantity: 1)],
        payments: [
            new PaymentLine(PaymentMethod::Cash, '60.00'),
            new PaymentLine(PaymentMethod::Card, '40.00'),
        ],
    );

    $charts = app(DashboardMetricsService::class)->charts($branch->id);

    expect($charts)
        ->toHaveKeys([
            'revenue_total',
            'revenue_line_path',
            'weekly_bars',
            'payment_donut',
            'top_product_bars',
        ])
        ->and((float) $charts['revenue_total'])->toBeGreaterThan(0)
        ->and($charts['weekly_bars'])->toHaveCount(7)
        ->and($charts['revenue_y_ticks'])->not->toBeEmpty()
        ->and($charts['payment_donut'])->not->toBeEmpty()
        ->and($charts['top_product_bars'][0]['label'])->toBe($product->name);
});

test('svg chart builder returns flat line for empty values', function () {
    $chart = SvgChartBuilder::lineChart([]);

    expect($chart)
        ->toHaveKeys(['line_path', 'area_path', 'total', 'y_ticks'])
        ->and($chart['total'])->toBe(0.0)
        ->and($chart['line_path'])->toContain('M 0')
        ->and($chart['y_ticks'])->toHaveCount(5);
});

test('svg chart builder formats y axis ticks for readable labels', function () {
    $chart = SvgChartBuilder::lineChart([12.5, 48.0, 100.0, 63.25]);

    expect($chart['y_ticks'])
        ->toHaveCount(5)
        ->and($chart['y_ticks'][0])->toBe('100')
        ->and($chart['y_ticks'][4])->toBe('0');
});

test('welcome livewire component renders chart sections', function () {
    createPharmacyContext();

    Livewire::test(Welcome::class)
        ->assertSee('Revenue (Last 14 Days)')
        ->assertSee('Payment Mix (Last 7 Days)');
});
