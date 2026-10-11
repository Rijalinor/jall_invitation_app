<?php

namespace App\Providers;

use App\Services\TemplateRegistry;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // One registry per request: its manifest cache and directory fingerprint
        // are then taken once no matter how many times a controller, renderer or
        // admin form asks for a template.
        $this->app->singleton(TemplateRegistry::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::addLocation(resource_path('invitation-templates'));
    }
}
