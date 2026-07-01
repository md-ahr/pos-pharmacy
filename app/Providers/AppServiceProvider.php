<?php

namespace App\Providers;

use App\Listeners\ClearPharmacyContextOnLogout;
use App\Listeners\SetPharmacyContextOnLogin;
use App\Livewire\Dashboard\Welcome;
use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
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

        RateLimiter::for('registration', function (Request $request): Limit {
            return Limit::perMinute(config('pharmacy.rate_limits.registration_per_minute', 5))
                ->by($request->ip());
        });

        View::composer(
            ['tyro-dashboard::partials.admin-sidebar', 'tyro-dashboard::partials.user-sidebar'],
            function ($view): void {
                $user = auth()->user();

                if ($user === null) {
                    return;
                }

                $view->with('commonMenuItems', $this->pharmacyMenuItemsFor($user));
            },
        );

        $this->app->booted(function (): void {
            $prefix = trim((string) config('tyro-dashboard.routes.prefix', 'dashboard'), '/');

            Route::middleware(array_merge(
                config('tyro-dashboard.routes.middleware', ['web', 'auth']),
                ['pharmacy.context'],
            ))
                ->get('/'.$prefix, Welcome::class)
                ->name('tyro-dashboard.index');
        });
    }

    /**
     * @return list<array{title: string, route: string, icon?: string}>
     */
    private function pharmacyMenuItemsFor(User $user): array
    {
        $items = [];

        foreach (config('menu.commonMenuItems', []) as $item) {
            $privilege = $item['privilege'] ?? null;
            $route = $item['route'] ?? null;

            if ($route === null || ! Route::has($route)) {
                continue;
            }

            if ($privilege !== null && (! method_exists($user, 'hasPrivilege') || ! $user->hasPrivilege($privilege))) {
                continue;
            }

            $items[] = [
                'title' => $item['title'],
                'route' => $route,
                'icon' => $item['icon'] ?? null,
            ];
        }

        return $items;
    }
}
