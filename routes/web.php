<?php

use App\Http\Controllers\Auth\RegisterController;
use App\Livewire\Dashboard\Welcome;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('tyro-dashboard.index');
    }

    return view('welcome');
});

Route::middleware(['web', 'guest'])->group(function (): void {
    if (config('tyro-login.registration.enabled', true)) {
        Route::get(config('tyro-login.routes.register', 'register'), [RegisterController::class, 'showRegistrationForm'])
            ->name('tyro-login.register');

        Route::post(config('tyro-login.routes.register', 'register'), [RegisterController::class, 'register'])
            ->name('tyro-login.register.submit');
    }
});

Route::middleware(['auth', 'pharmacy.context'])->group(function (): void {
    Route::get('/pos', fn () => view('pharmacy.stub', ['title' => 'POS']))
        ->middleware('privilege:pos.access')
        ->name('pharmacy.pos');

    Route::get('/inventory', fn () => view('pharmacy.stub', ['title' => 'Inventory']))
        ->middleware('privilege:inventory.manage')
        ->name('pharmacy.inventory');

    Route::get('/reports', fn () => view('pharmacy.stub', ['title' => 'Reports']))
        ->middleware('privilege:reports.view')
        ->name('pharmacy.reports');

    Route::get('/settings', fn () => view('pharmacy.stub', ['title' => 'Settings']))
        ->middleware('privilege:settings.manage')
        ->name('pharmacy.settings');

    Route::get('/dashboard/welcome', Welcome::class)
        ->name('pharmacy.dashboard.welcome');
});
