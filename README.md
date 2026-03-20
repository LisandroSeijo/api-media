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
GIPHY_BASE_URL=https://api.giphy.com/v1

MEDIA_CACHE_ENABLED=false
MEDIA_CACHE_TTL_MINUTES=60
MEDIA_CACHE_DRIVER=redis

# Edita tu archivo .env y agrega:
GIPHY_API_KEY=
GIPHY_BASE_URL=https://api.giphy.com/v1

MEDIA_CACHE_ENABLED=false
MEDIA_CACHE_TTL_MINUTES=60
MEDIA_CACHE_DRIVER=redis
```

4. Instalar proyecto:
```bash
./install.sh
```
> **ACLARACIÓN:**  este comando levanta docker, instala composer, y corre todos los comandos necesarios. Es necesario para fixear la instalación de passport.

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


**Regenerar documentación:**
```bash
docker-compose exec app php artisan l5-swagger:generate
```

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

Ver documentación detallada en:
- [`docs/diagrams/`](docs/diagrams/) - Diagramas UML

## Servicios Docker

| Servicio | Puerto | Descripción |
|----------|--------|-------------|
| `nginx` | 8000 | API |
| `db` | 3306 | MySQL 8.0 |
| `redis` | 6379 | Redis 7-alpine |
| `phpmyadmin` | 8080 | Administrador de base de datos |

## URLs

- **API**: http://localhost:8000
- **Swagger Documentation**: http://localhost:8000/api/documentation
- **PHPMyAdmin**: http://localhost:8080