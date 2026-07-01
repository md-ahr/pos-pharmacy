<?php

use App\Models\Tenant;
use App\Services\InvoiceNumberService;
use Database\Seeders\PharmacyRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(PharmacyRoleSeeder::class);
});

test('generates sequential invoice numbers per tenant', function () {
    $tenant = Tenant::factory()->create();
    $service = app(InvoiceNumberService::class);
    $year = (int) now()->format('Y');

    $first = $service->next($tenant);
    $second = $service->next($tenant);

    expect($first)->toBe(sprintf('INV-%d-%06d', $year, 1))
        ->and($second)->toBe(sprintf('INV-%d-%06d', $year, 2));
});

test('each tenant maintains an independent invoice counter', function () {
    $tenantA = Tenant::factory()->create();
    $tenantB = Tenant::factory()->create();
    $service = app(InvoiceNumberService::class);
    $year = (int) now()->format('Y');

    $numberA = $service->next($tenantA);
    $numberB = $service->next($tenantB);
    $numberA2 = $service->next($tenantA);

    expect($numberA)->toBe(sprintf('INV-%d-%06d', $year, 1))
        ->and($numberB)->toBe(sprintf('INV-%d-%06d', $year, 1))
        ->and($numberA2)->toBe(sprintf('INV-%d-%06d', $year, 2));
});
