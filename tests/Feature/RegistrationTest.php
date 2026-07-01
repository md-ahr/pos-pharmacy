<?php

use App\Models\Branch;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\PharmacyRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(PharmacyRoleSeeder::class);
});

test('self serve registration creates tenant main branch and owner atomically', function () {
    $response = $this->post(route('tyro-login.register.submit'), [
        'pharmacy_name' => 'Sunrise Pharmacy',
        'name' => 'Ada Owner',
        'email' => 'ada@sunrise.test',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect(config('tyro-login.redirects.after_register', '/dashboard'));

    $tenant = Tenant::query()->where('slug', 'sunrise-pharmacy')->first();
    $user = User::query()->where('email', 'ada@sunrise.test')->first();

    expect($tenant)->not->toBeNull()
        ->and($tenant->name)->toBe('Sunrise Pharmacy')
        ->and($tenant->branches()->where('is_main', true)->count())->toBe(1)
        ->and($user)->not->toBeNull()
        ->and($user->tenant_id)->toBe($tenant->id)
        ->and($user->branch_id)->toBeNull()
        ->and($user->role)->toBe('owner')
        ->and($user->hasRole('owner'))->toBeTrue()
        ->and(Hash::check('password', $user->password))->toBeTrue();
});

test('registration requires pharmacy name', function () {
    $response = $this->from(route('tyro-login.register'))
        ->post(route('tyro-login.register.submit'), [
            'name' => 'Ada Owner',
            'email' => 'ada@sunrise.test',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

    $response->assertRedirect(route('tyro-login.register'))
        ->assertSessionHasErrors('pharmacy_name');

    expect(Tenant::query()->count())->toBe(0)
        ->and(User::query()->where('email', 'ada@sunrise.test')->exists())->toBeFalse();
});

test('registration rolls back when user creation fails', function () {
    $this->post(route('tyro-login.register.submit'), [
        'pharmacy_name' => 'Rollback Pharmacy',
        'name' => 'Broken User',
        'email' => 'not-an-email',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertSessionHasErrors('email');

    expect(Tenant::query()->where('name', 'Rollback Pharmacy')->exists())->toBeFalse()
        ->and(Branch::query()->count())->toBe(0);
});
