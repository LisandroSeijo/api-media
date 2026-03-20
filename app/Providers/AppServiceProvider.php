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

        // Shared - Bind Cache Service Interface to Laravel Implementation
        $this->app->singleton(
            \Api\Shared\Domain\Services\CacheServiceInterface::class,
            function ($app) {
                return new \Api\Shared\Infrastructure\Services\LaravelCacheService(
                    config('media.cache.driver')
                );
            }
        );

        // Media Module - Bind SearchMedia Use Case configuration
        $this->app->when(\Api\Media\Application\UseCases\SearchMedia::class)
            ->needs('$cacheEnabled')
            ->give(fn() => config('media.cache.enabled'));
        
        $this->app->when(\Api\Media\Application\UseCases\SearchMedia::class)
            ->needs('$cacheTtlMinutes')
            ->give(fn() => config('media.cache.ttl_minutes'));

        // Media Module - Bind GetMediaById Use Case configuration
        $this->app->when(\Api\Media\Application\UseCases\GetMediaById::class)
            ->needs('$cacheEnabled')
            ->give(fn() => config('media.cache.enabled'));
        
        $this->app->when(\Api\Media\Application\UseCases\GetMediaById::class)
            ->needs('$cacheTtlMinutes')
            ->give(fn() => config('media.cache.ttl_minutes'));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
