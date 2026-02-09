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
                \App\Modules\HR\Infrastructure\Console\Commands\AutoCheckoutCommand::class,
            ]);
        }

        try {
            \Illuminate\Support\Facades\Storage::extend('google', function ($app, $config) {
                $client = new \Google\Client();
                $client->setClientId($config['clientId']);
                $client->setClientSecret($config['clientSecret']);
                $client->refreshToken($config['refreshToken']);

                $service = new \Google\Service\Drive($client);
                $adapter = new \Masbug\Flysystem\GoogleDriveAdapter($service, $config['folder'] ?? '/');
                $driver = new \League\Flysystem\Filesystem($adapter);

                return new \Illuminate\Filesystem\FilesystemAdapter($driver, $adapter);
            });
        } catch (\Exception $e) {
            // quiet failure if google drive dependencies missing or config invalid during boot
        }
    }
}
