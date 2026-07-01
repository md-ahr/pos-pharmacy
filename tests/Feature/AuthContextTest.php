<?php

use App\Models\Branch;
use App\Models\Tenant;
use App\Models\User;
use App\Services\BranchContext;
use Database\Seeders\PharmacyRoleSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(PharmacyRoleSeeder::class);
});

test('login initializes tenant and branch session context', function () {
    $tenant = Tenant::factory()->create();
    $branch = Branch::factory()->main()->create(['tenant_id' => $tenant->id]);
    $user = User::factory()->cashier($tenant, $branch)->create([
        'email' => 'cashier@pharmacy.test',
        'password' => bcrypt('password'),
    ]);

    $this->post(route('tyro-login.login.submit'), [
        'email' => 'cashier@pharmacy.test',
        'password' => 'password',
    ])->assertRedirect(config('tyro-login.redirects.after_login', '/dashboard'));

    expect(Session::get(config('pharmacy.session.tenant_id')))->toBe($tenant->id)
        ->and(Session::get(config('pharmacy.session.branch_id')))->toBe($branch->id);
});

test('owner can switch active branch in session', function () {
    $tenant = Tenant::factory()->create();
    $mainBranch = Branch::factory()->main()->create(['tenant_id' => $tenant->id]);
    $secondBranch = Branch::factory()->create([
        'tenant_id' => $tenant->id,
        'name' => 'Downtown Branch',
        'code' => 'DT01',
    ]);
    $owner = User::factory()->owner($tenant)->create();

    $this->actingAs($owner);
    app(BranchContext::class)->initialize($owner, $mainBranch->id);

    app(BranchContext::class)->switchBranch($owner, $secondBranch->id);

    expect(Session::get(config('pharmacy.session.branch_id')))->toBe($secondBranch->id);
});

test('cashier cannot switch branches', function () {
    $tenant = Tenant::factory()->create();
    $branch = Branch::factory()->main()->create(['tenant_id' => $tenant->id]);
    $otherBranch = Branch::factory()->create(['tenant_id' => $tenant->id]);
    $cashier = User::factory()->cashier($tenant, $branch)->create();

    $this->actingAs($cashier);
    app(BranchContext::class)->initialize($cashier, $branch->id);

    expect(fn () => app(BranchContext::class)->switchBranch($cashier, $otherBranch->id))
        ->toThrow(AuthorizationException::class);
});

test('privilege middleware blocks unauthorized pharmacy routes', function () {
    $tenant = Tenant::factory()->create();
    $branch = Branch::factory()->main()->create(['tenant_id' => $tenant->id]);
    $cashier = User::factory()->cashier($tenant, $branch)->create();

    $this->actingAs($cashier);
    app(BranchContext::class)->initialize($cashier, $branch->id);

    $this->get(route('pharmacy.pos'))->assertOk();
    $this->get(route('pharmacy.inventory'))->assertForbidden();
    $this->get(route('pharmacy.settings'))->assertForbidden();
});

test('owner can access settings route', function () {
    $tenant = Tenant::factory()->create();
    $branch = Branch::factory()->main()->create(['tenant_id' => $tenant->id]);
    $owner = User::factory()->owner($tenant)->create();

    $this->actingAs($owner);
    app(BranchContext::class)->initialize($owner, $branch->id);

    $this->get(route('pharmacy.settings'))
        ->assertRedirect(route('pharmacy.settings.general'));

    $this->get(route('pharmacy.settings.general'))->assertOk();
});

test('logout clears pharmacy session context', function () {
    $tenant = Tenant::factory()->create();
    $branch = Branch::factory()->main()->create(['tenant_id' => $tenant->id]);
    $owner = User::factory()->owner($tenant)->create([
        'email' => 'owner@pharmacy.test',
        'password' => bcrypt('password'),
    ]);

    $this->post(route('tyro-login.login.submit'), [
        'email' => 'owner@pharmacy.test',
        'password' => 'password',
    ]);

    expect(Session::has(config('pharmacy.session.tenant_id')))->toBeTrue();

    $this->post(route('tyro-login.logout'));

    expect(Session::has(config('pharmacy.session.tenant_id')))->toBeFalse()
        ->and(Session::has(config('pharmacy.session.branch_id')))->toBeFalse();
});
