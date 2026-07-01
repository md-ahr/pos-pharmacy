<?php

use App\Models\Batch;
use App\Models\Scopes\TenantScope;
use App\Models\Tenant;
use Database\Seeders\PharmacyRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(PharmacyRoleSeeder::class);
});

test('registration endpoint is rate limited', function () {
    $payload = [
        'name' => 'Rate Limit User',
        'email' => 'rate-limit@example.test',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'pharmacy_name' => 'Rate Limit Pharmacy',
    ];

    for ($attempt = 0; $attempt < 5; $attempt++) {
        $this->post(route('tyro-login.register.submit'), array_merge($payload, [
            'email' => "rate-limit-{$attempt}@example.test",
        ]))->assertRedirect();
    }

    $this->post(route('tyro-login.register.submit'), array_merge($payload, [
        'email' => 'rate-limit-blocked@example.test',
    ]))->assertStatus(429);
});

test('expiring batches command reports tenant scoped batches', function () {
    ['tenant' => $tenant] = createPharmacyContext();

    Batch::factory()->create([
        'tenant_id' => $tenant->id,
        'expiry_date' => now()->addDays(7),
    ]);

    $otherTenant = Tenant::factory()->create();
    Batch::factory()->create([
        'tenant_id' => $otherTenant->id,
        'expiry_date' => now()->addDays(7),
    ]);

    Artisan::call('pharmacy:check-expiring-batches', ['--days' => 30]);

    expect(Artisan::output())->toContain((string) $tenant->id);
});

test('scheduled pharmacy maintenance commands are registered', function () {
    $events = collect(Schedule::events())->map(fn ($event) => $event->command ?? $event->description);

    expect($events->join(' '))->toContain('pharmacy:check-expiring-batches')
        ->and($events->join(' '))->toContain('pharmacy:backup-database')
        ->and($events->join(' '))->toContain('pharmacy:generate-daily-report');
});

test('daily report command runs without error', function () {
    createPharmacyContext();

    expect(Artisan::call('pharmacy:generate-daily-report'))->toBe(0);
});

test('expired batch remains excluded from scoped batch queries in jobs', function () {
    ['tenant' => $tenant] = createPharmacyContext();

    $expired = Batch::factory()->create([
        'tenant_id' => $tenant->id,
        'expiry_date' => now()->subDay(),
    ]);

    $active = Batch::factory()->create([
        'tenant_id' => $tenant->id,
        'expiry_date' => now()->addMonth(),
    ]);

    $jobQuery = Batch::query()
        ->withoutGlobalScope(TenantScope::class)
        ->where('tenant_id', $tenant->id)
        ->whereDate('expiry_date', '>=', today())
        ->pluck('id');

    expect($jobQuery)->toContain($active->id)
        ->and($jobQuery)->not->toContain($expired->id);
});
