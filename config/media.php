<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Media Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configuración del sistema de caché para el módulo Media.
    | Permite cachear respuestas de GIPHY API para mejorar performance
    | y reducir llamadas a servicios externos.
    |
    */

    'cache' => [
        /**
         * Habilita o deshabilita el sistema de cache para Media
         */
        'enabled' => env('MEDIA_CACHE_ENABLED', true),

        /**
         * Tiempo de vida del cache en minutos
         * Default: 60 minutos (1 hora)
         */
        'ttl_minutes' => env('MEDIA_CACHE_TTL_MINUTES', 60),

        /**
         * Driver de cache a utilizar
         * Opciones: redis, file, array, memcached
         * Default: redis
         */
        'driver' => env('MEDIA_CACHE_DRIVER', 'redis'),
    ],
];
