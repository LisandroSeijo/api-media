<?php

declare(strict_types=1);

namespace Api\Shared\Infrastructure\Services;

use Api\Shared\Domain\Services\CacheServiceInterface;
use Illuminate\Support\Facades\Cache;

/**
 * Implementación del servicio de cache usando Laravel Cache
 * 
 * Permite usar diferentes drivers (Redis, Memcached, File, etc.)
 * configurados en Laravel sin cambiar el código del dominio.
 */
final class LaravelCacheService implements CacheServiceInterface
{
    public function __construct(
        private readonly string $driver
    ) {}

    public function get(string $key): mixed
    {
        return Cache::driver($this->driver)->get($key);
    }

    public function put(string $key, mixed $value, int $ttlMinutes): void
    {
        Cache::driver($this->driver)->put($key, $value, now()->addMinutes($ttlMinutes));
    }

    public function has(string $key): bool
    {
        return Cache::driver($this->driver)->has($key);
    }

    public function forget(string $key): void
    {
        Cache::driver($this->driver)->forget($key);
    }

    public function flush(): void
    {
        Cache::driver($this->driver)->flush();
    }
}
