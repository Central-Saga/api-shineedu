<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Modules\HR\Repositories\EmployeeRepositoryInterface::class,
            \App\Modules\HR\Repositories\EmployeeRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\RateLimiter::for('emails', function (object $job) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(30);
        });

        Gate::before(function ($user, $ability) {
            return $user?->hasRole('Superadmin') ? true : null;
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                \App\Modules\Identity\Infrastructure\Console\Commands\ImportUsersCommand::class,
            ]);
        }
    }
}
