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

        // Auth Module - Bind TokenService Interface to Passport Implementation
        $this->app->bind(
            \Api\Auth\Domain\Services\TokenServiceInterface::class,
            \Api\Auth\Infrastructure\Services\PassportTokenService::class
        );

        // Media Module - Bind Repository Interface to GIPHY Implementation
        $this->app->bind(
            \Api\Media\Domain\Repositories\MediaRepositoryInterface::class,
            \Api\Media\Infrastructure\Persistence\Http\GiphyMediaRepository::class
        );

        // Media Module - Bind Guzzle Client (generic, configured per-repository)
        $this->app->when(\Api\Media\Infrastructure\Persistence\Http\GiphyMediaRepository::class)
            ->needs(\GuzzleHttp\Client::class)
            ->give(function () {
                return new \GuzzleHttp\Client();
            });

        // Audit Module - Bind Repository Interface to Eloquent Implementation
        $this->app->bind(
            \Api\Audit\Domain\Repositories\AuditLogRepositoryInterface::class,
            \Api\Audit\Infrastructure\Persistence\Eloquent\Repositories\EloquentAuditLogRepository::class
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
