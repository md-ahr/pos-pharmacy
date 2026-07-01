<?php

use App\Livewire\Dashboard\Welcome;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;

test('tyro login routes are registered', function () {
    expect(Route::has('tyro-login.login'))->toBeTrue()
        ->and(Route::has('tyro-login.register'))->toBeTrue()
        ->and(Route::has('tyro-login.logout'))->toBeTrue();
});

test('tyro dashboard route is registered and requires authentication', function () {
    expect(Route::has('tyro-dashboard.index'))->toBeTrue();

    $this->get(route('tyro-dashboard.index'))
        ->assertRedirect();
});

test('livewire is installed and class-based components resolve', function () {
    expect(class_exists(Livewire::class))->toBeTrue()
        ->and(class_exists(Welcome::class))->toBeTrue();

    Livewire::test(Welcome::class)
        ->assertStatus(200);
});

test('postgresql is the documented default database driver', function () {
    $envExample = file_get_contents(base_path('.env.example'));

    expect($envExample)
        ->toContain('DB_CONNECTION=pgsql')
        ->toContain('DB_DATABASE=pos_pharmecy');
});
