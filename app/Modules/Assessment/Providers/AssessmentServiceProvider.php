<?php

namespace App\Modules\Assessment\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AssessmentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Load Views
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'assessment');

        // Load Migrations (if strictly modular, though Laravel usually auto-discovers database/migrations)
        // $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
    }
}
