<?php

namespace App\Providers;

use App\Listeners\ClearPharmacyContextOnLogout;
use App\Listeners\SetPharmacyContextOnLogin;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(Login::class, SetPharmacyContextOnLogin::class);
        Event::listen(Logout::class, ClearPharmacyContextOnLogout::class);
    }
}
