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

        // Media Module - Bind Repository Interface to GIPHY Implementation
        $this->app->bind(
            \Api\Media\Domain\Repositories\MediaRepositoryInterface::class,
            \Api\Media\Infrastructure\Persistence\Http\GiphyMediaRepository::class
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
