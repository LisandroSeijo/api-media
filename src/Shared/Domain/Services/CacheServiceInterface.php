<?php

declare(strict_types=1);

namespace Api\Shared\Domain\Services;

/**
 * Interfaz para abstracción del sistema de caché
 * 
 * Permite implementar diferentes drivers de cache (Redis, Memcached, etc.)
 * sin acoplar el dominio a una implementación específica.
 */
interface CacheServiceInterface
{
    /**
     * Obtiene un valor del cache
     *
     * @param string $key Clave del cache
     * @return mixed Valor almacenado o null si no existe
     */
    public function get(string $key): mixed;
    
    /**
     * Almacena un valor en el cache
     *
     * @param string $key Clave del cache
     * @param mixed $value Valor a almacenar
     * @param int $ttlMinutes Tiempo de vida en minutos
     */
    public function put(string $key, mixed $value, int $ttlMinutes): void;
    
    /**
     * Verifica si existe una clave en el cache
     *
     * @param string $key Clave del cache
     * @return bool True si existe, false en caso contrario
     */
    public function has(string $key): bool;
    
    /**
     * Elimina una clave específica del cache
     *
     * @param string $key Clave del cache
     */
    public function forget(string $key): void;
    
    /**
     * Limpia todo el cache
     */
    public function flush(): void;
}
