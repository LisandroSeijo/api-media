# Variables de Entorno - Configuración

## Variables de Redis Cache

### MEDIA_CACHE_ENABLED
**Descripción**: Habilita o deshabilita el sistema de cache para endpoints de Media  
**Tipo**: Boolean  
**Default**: `true`  
**Valores posibles**: 
- `true` - Cache habilitado
- `false` - Cache deshabilitado

**Ejemplo**:
```env
MEDIA_CACHE_ENABLED=true
```

### MEDIA_CACHE_TTL_MINUTES
**Descripción**: Tiempo de vida del cache en minutos. Define cuánto tiempo se mantienen las respuestas cacheadas antes de expirar.  
**Tipo**: Integer  
**Default**: `60` (1 hora)  
**Valores sugeridos**:
- `30` - Media hora (para datos que cambian frecuentemente)
- `60` - 1 hora (recomendado para uso general)
- `120` - 2 horas
- `1440` - 1 día (para datos muy estables)

**Ejemplo**:
```env
MEDIA_CACHE_TTL_MINUTES=60
```

**Consideraciones**:
- Valores más altos reducen llamadas a GIPHY pero pueden mostrar datos desactualizados
- Valores más bajos aumentan la frecuencia de llamadas a GIPHY pero garantizan datos más frescos

### MEDIA_CACHE_DRIVER
**Descripción**: Driver de cache a utilizar  
**Tipo**: String  
**Default**: `redis`  
**Valores posibles**:
- `redis` - Recomendado para producción (requiere servicio Redis)
- `file` - Almacena cache en archivos (útil para desarrollo)
- `array` - Cache en memoria (solo para tests)
- `memcached` - Alternativa a Redis

**Ejemplo**:
```env
MEDIA_CACHE_DRIVER=redis
```

## Variables de Redis

### REDIS_HOST
**Descripción**: Host del servidor Redis  
**Tipo**: String  
**Default**: `127.0.0.1`  
**Valor en Docker**: `redis`

**Ejemplo**:
```env
REDIS_HOST=redis
```

### REDIS_PORT
**Descripción**: Puerto del servidor Redis  
**Tipo**: Integer  
**Default**: `6379`

**Ejemplo**:
```env
REDIS_PORT=6379
```

### REDIS_PASSWORD
**Descripción**: Contraseña para Redis (si está configurada)  
**Tipo**: String  
**Default**: `null`

**Ejemplo**:
```env
REDIS_PASSWORD=null
```

### REDIS_CLIENT
**Descripción**: Cliente de PHP para Redis  
**Tipo**: String  
**Default**: `phpredis`  
**Valores posibles**:
- `phpredis` - Extensión PHP Redis (más rápido)
- `predis` - Cliente PHP puro

**Ejemplo**:
```env
REDIS_CLIENT=phpredis
```

## Variables de Laravel Cache (existentes)

### CACHE_STORE
**Descripción**: Store de cache por defecto de Laravel  
**Tipo**: String  
**Recomendado**: `redis` (cuando Redis está disponible)

**Ejemplo**:
```env
CACHE_STORE=redis
```

### CACHE_PREFIX
**Descripción**: Prefijo para todas las keys de cache  
**Tipo**: String  
**Default**: `laravel-cache-`

**Ejemplo**:
```env
CACHE_PREFIX=laravel-cache-
```

## Configuración Completa para .env

Agregar estas líneas a tu archivo `.env`:

```env
# Redis Configuration
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_CLIENT=phpredis

# Cache Store
CACHE_STORE=redis
CACHE_PREFIX=laravel-cache-

# Media Cache Configuration
MEDIA_CACHE_ENABLED=true
MEDIA_CACHE_TTL_MINUTES=60
MEDIA_CACHE_DRIVER=redis
```

## Comandos Útiles

### Ver estado del cache
```bash
# Ver todas las keys de Media en Redis
docker-compose exec redis redis-cli KEYS "laravel-cache-media:*"

# Contar cuántas keys de Media hay
docker-compose exec redis redis-cli KEYS "laravel-cache-media:*" | wc -l

# Ver estadísticas de Redis
docker-compose exec redis redis-cli INFO stats

# Ver memoria usada por Redis
docker-compose exec redis redis-cli INFO memory
```

### Gestionar el cache
```bash
# Limpiar cache de Media
docker-compose exec app php artisan media:cache:clear

# Limpiar todo el cache de Laravel
docker-compose exec app php artisan cache:clear

# Ver configuración de cache
docker-compose exec app php artisan config:show cache
```

### Debug
```bash
# Conectar a Redis CLI
docker-compose exec redis redis-cli

# Dentro de Redis CLI:
# Ver todas las keys
KEYS *

# Ver valor de una key específica
GET laravel-cache-media:search:abc123

# Ver TTL de una key
TTL laravel-cache-media:search:abc123

# Eliminar una key específica
DEL laravel-cache-media:search:abc123

# Limpiar toda la base de datos Redis
FLUSHDB
```

## Testing

Durante los tests, el cache está mockeado automáticamente para evitar dependencias externas y garantizar pruebas determinísticas.

## Performance

Con Redis cache habilitado:
- **Sin cache**: ~100-300ms por request (llamada a GIPHY API)
- **Con cache**: <5ms por request (lectura de Redis)
- **Mejora**: ~20-60x más rápido

## Troubleshooting

### Cache no funciona
1. Verificar que Redis está corriendo:
```bash
docker-compose ps redis
```

2. Verificar conexión a Redis:
```bash
docker-compose exec app php artisan tinker
>>> Cache::driver('redis')->put('test', 'value', 60);
>>> Cache::driver('redis')->get('test');
```

3. Verificar variables de entorno:
```bash
docker-compose exec app php artisan config:show media
```

### Redis usa mucha memoria
1. Reducir `MEDIA_CACHE_TTL_MINUTES`
2. Limpiar cache manualmente: `php artisan media:cache:clear`
3. Configurar Redis con `maxmemory` y `maxmemory-policy`
