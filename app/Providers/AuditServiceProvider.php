<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Api\Audit\Infrastructure\Listeners\LogRequestAudited;

class AuditServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Registrar el listener para el evento RequestHandled
        $this->app['events']->listen(
            RequestHandled::class,
            LogRequestAudited::class
        );
    }
}
