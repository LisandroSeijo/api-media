<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Auth Module - Bind Repository Interface to Eloquent Implementation
        $this->app->bind(
            \Api\Auth\Domain\Repositories\UserRepositoryInterface::class,
            \Api\Auth\Infrastructure\Persistence\Eloquent\Repositories\EloquentUserRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
