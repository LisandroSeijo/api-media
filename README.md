# Laravel API con Redis Cache

API REST construida con Laravel 12, OAuth2.0 (Passport), Doctrine ORM y Redis Cache, siguiendo arquitectura hexagonal.

## Stack Tecnológico

- **PHP** 8.3
- **Laravel Framework** 12.x
- **MySQL** 8.0
- **Redis** 7-alpine
- **Laravel Passport** (OAuth2.0)
- **Doctrine ORM** 3.6.2
- **Docker & Docker Compose**
- **Nginx**

## Características

- ✅ Arquitectura Hexagonal (Domain, Application, Infrastructure)
- ✅ Autenticación OAuth2.0 con Laravel Passport
- ✅ Sistema de roles (Admin/User)
- ✅ Integración con GIPHY API
- ✅ Cache Redis para optimización de performance
- ✅ Audit logs completos
- ✅ Tests (Unit, Feature, E2E)
- ✅ Specifications Pattern para validaciones
- ✅ Value Objects y Entities
- ✅ Dependency Injection
- ✅ **Documentación Swagger/OpenAPI 3.0**

## Instalación

### Prerrequisitos

- Docker & Docker Compose
- Git

### Pasos

1. Clonar el repositorio:
```bash
git clone <repository-url>
cd api
```

2. Copiar archivo de configuración:
```bash
cp .env.example .env
```

3. Configurar variables de entorno en `.env`:
```env
# Database
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=root

# Redis
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_CLIENT=phpredis

# Media Cache Configuration
MEDIA_CACHE_ENABLED=true
MEDIA_CACHE_TTL_MINUTES=60
MEDIA_CACHE_DRIVER=redis

# GIPHY API
GIPHY_API_KEY=your_giphy_api_key_here
```

4. Levantar contenedores Docker:
```bash
docker-compose up -d
```

5. Instalar dependencias de Composer:
```bash
docker-compose exec app composer install
```

6. Generar key de aplicación:
```bash
docker-compose exec app php artisan key:generate
```

7. Ejecutar migraciones:
```bash
docker-compose exec app php artisan migrate
```

8. Instalar Laravel Passport:
```bash
docker-compose exec app php artisan passport:install
```

9. Crear usuario administrador:
```bash
docker-compose exec app php artisan create:admin
```

## Uso

### Documentación Interactiva (Swagger UI)

La API cuenta con documentación interactiva completa gracias a Swagger/OpenAPI 3.0.

**Acceder a Swagger UI:**
```
http://localhost:8000/api/documentation
```

Desde Swagger UI puedes:
- 📖 Ver todos los endpoints disponibles
- 🔍 Explorar request/response schemas
- 🧪 Probar endpoints directamente desde el navegador
- 🔐 Autenticarte con tu token Bearer
- 📋 Ver ejemplos de uso para cada endpoint

**Regenerar documentación:**
```bash
docker-compose exec app php artisan l5-swagger:generate
```

### Endpoints disponibles

Ver documentación completa en [`API_ENDPOINTS.md`](API_ENDPOINTS.md)

#### Autenticación
- `POST /api/v1/login` - Login de usuario
- `POST /api/v1/logout` - Logout de usuario
- `GET /api/v1/user` - Obtener usuario autenticado
- `POST /api/v1/register` - Registrar usuario (solo admin)

#### Media
- `GET /api/v1/media/search` - Buscar media (con cache Redis)
- `GET /api/v1/media/{id}` - Obtener media por ID (con cache Redis)

#### Sistema
- `GET /api/v1/health` - Health check

### Cache Redis

El sistema utiliza Redis para cachear las respuestas de GIPHY API, mejorando significativamente la performance:

- **Performance**: Reduce latencia de ~100-300ms a <5ms
- **TTL configurable**: Ajustable desde `.env` con `MEDIA_CACHE_TTL_MINUTES`
- **Invalidación automática**: El cache expira automáticamente después del TTL

#### Configuración del Cache

Variables disponibles en `.env`:

| Variable | Descripción | Default | Valores |
|----------|-------------|---------|---------|
| `MEDIA_CACHE_ENABLED` | Habilita/deshabilita cache | `true` | `true`, `false` |
| `MEDIA_CACHE_TTL_MINUTES` | TTL en minutos | `60` | Cualquier entero positivo |
| `MEDIA_CACHE_DRIVER` | Driver de cache | `redis` | `redis`, `file`, `array` |

#### Comandos de Cache

```bash
# Limpiar cache de Media
docker-compose exec app php artisan media:cache:clear

# Ver todas las keys de Media en Redis
docker-compose exec redis redis-cli KEYS "laravel-cache-media:*"

# Conectar a Redis CLI
docker-compose exec redis redis-cli

# Ver estadísticas de Redis
docker-compose exec redis redis-cli INFO stats
```

## Tests

Ejecutar todos los tests:
```bash
docker-compose exec app php artisan test
```

Ejecutar tests específicos:
```bash
# Unit tests
docker-compose exec app php artisan test --testsuite=Unit

# Feature tests
docker-compose exec app php artisan test --testsuite=Feature

# E2E tests
docker-compose exec app php artisan test --testsuite=E2E

# Tests de un módulo específico
docker-compose exec app php artisan test --filter=Media
```

## Arquitectura

El proyecto sigue arquitectura hexagonal organizada por módulos:

```
src/
├── Auth/           # Módulo de autenticación
├── Media/          # Módulo de búsqueda de media
├── Audit/          # Módulo de auditoría
├── System/         # Módulo de sistema
└── Shared/         # Código compartido
    ├── Domain/
    │   ├── Services/
    │   │   └── CacheServiceInterface.php
    │   └── Specifications/
    └── Infrastructure/
        └── Services/
            └── LaravelCacheService.php
```

Ver documentación detallada en:
- [`docs/diagrams/`](docs/diagrams/) - Diagramas UML
- `README_ARCHITECTURE.md` - Arquitectura detallada

## Servicios Docker

| Servicio | Puerto | Descripción |
|----------|--------|-------------|
| `app` | - | Aplicación Laravel (PHP 8.3) |
| `nginx` | 8000 | Servidor web |
| `db` | 3306 | MySQL 8.0 |
| `redis` | 6379 | Redis 7-alpine |
| `phpmyadmin` | 8080 | Administrador de base de datos |

## URLs

- **API**: http://localhost:8000
- **Swagger Documentation**: http://localhost:8000/api/documentation
- **PHPMyAdmin**: http://localhost:8080

## Troubleshooting

### Error: "Class Redis not found"

Si encuentras el error `Class "Redis" not found`, significa que la extensión PHP Redis no está instalada. 

**Solución:**

1. Verifica que el `Dockerfile` incluya la instalación de Redis:
```dockerfile
# Instalar Redis extension
RUN pecl install redis \
    && docker-php-ext-enable redis
```

2. Reconstruye el contenedor:
```bash
docker-compose down
docker-compose build --no-cache app
docker-compose up -d
```

3. Verifica que Redis esté instalado:
```bash
docker-compose exec app php -m | grep redis
# Debería mostrar: redis
```

4. Prueba la conexión:
```bash
docker-compose exec app php artisan tinker --execute="Cache::put('test', 'ok', 60); echo Cache::get('test');"
# Debería mostrar: ok
```

### Redis no está respondiendo

```bash
# Verificar estado de Redis
docker-compose ps redis

# Ver logs de Redis
docker-compose logs redis --tail 50

# Reiniciar Redis
docker-compose restart redis

# Probar conexión
docker-compose exec redis redis-cli ping
# Debería mostrar: PONG
```

### Limpiar caché

```bash
# Limpiar cache de Laravel
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear

# Ver claves en Redis
docker-compose exec redis redis-cli KEYS "*"

# Limpiar toda la base de datos de Redis (¡cuidado!)
docker-compose exec redis redis-cli FLUSHDB
```

### Problemas de permisos

```bash
# Dar permisos correctos
docker-compose exec app chown -R laravel:laravel /var/www/storage
docker-compose exec app chmod -R 775 /var/www/storage
```

## Licencia

[Especificar licencia]

## Contribuir

[Instrucciones para contribuir]
