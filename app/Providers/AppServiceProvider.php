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
            \Src\Auth\Domain\Repositories\UserRepositoryInterface::class,
            \Src\Auth\Infrastructure\Persistence\Eloquent\Repositories\EloquentUserRepository::class
        );

        // Post Module - Bind Repository Interface to Eloquent Implementation
        $this->app->bind(
            \Src\Post\Domain\Repositories\PostRepositoryInterface::class,
            \Src\Post\Infrastructure\Persistence\Eloquent\Repositories\EloquentPostRepository::class
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
