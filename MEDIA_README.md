# 🎯 Integración Media API (GIPHY) - Guía Rápida

## ✅ Módulo Media Implementado

Módulo de **Media** con arquitectura hexagonal que permite buscar GIFs, stickers y contenido animado desde proveedores externos (actualmente GIPHY).

---

## 🚀 Activación en 3 Pasos

### 1️⃣ Instalar Guzzle HTTP Client

```bash
docker-compose up -d
docker-compose exec app composer install
```

### 2️⃣ Obtener API Key de GIPHY

1. Ve a: https://developers.giphy.com/
2. Crea una cuenta
3. Crea una app en el Dashboard
4. Copia tu API Key

### 3️⃣ Configurar `.env`

Agrega al final de tu archivo `.env`:

```env
# GIPHY API Configuration
GIPHY_API_KEY=tu_api_key_aqui
GIPHY_BASE_URL=https://api.giphy.com/v1
```

Luego limpia el cache:

```bash
docker-compose exec app php artisan config:clear
```

---

## 🎬 Probar la Integración

### Obtener Token

```bash
curl -X POST "http://localhost:8000/api/v1/login" \
  -H "Content-Type: application/json" \
  -d '{"email":"tu@email.com","password":"password"}' \
  | jq -r '.data.access_token'
```

### Buscar Media

```bash
curl -X GET "http://localhost:8000/api/v1/media/search?query=funny+cats&limit=5" \
  -H "Authorization: Bearer TU_TOKEN" \
  -H "Accept: application/json" \
  | jq
```

---

## 📁 Estructura del Módulo

```
src/Media/
├── Domain/
│   ├── Entities/
│   │   └── MediaItem.php               # Entidad de media
│   ├── ValueObjects/
│   │   ├── SearchQuery.php             # VO para búsqueda
│   │   ├── Limit.php                   # VO para límite
│   │   └── Offset.php                  # VO para paginación
│   └── Repositories/
│       └── MediaRepositoryInterface.php # Contrato del repositorio
├── Application/
│   ├── UseCases/
│   │   └── SearchMedia.php             # Caso de uso: buscar
│   └── DTOs/
│       └── SearchMediaDTO.php          # DTO para transferencia
└── Infrastructure/
    ├── Persistence/
    │   └── Http/
    │       └── GiphyMediaRepository.php # Implementación GIPHY
    └── Http/
        └── Controllers/
            └── MediaController.php      # Controlador REST
```

**Configuración actualizada:**
- ✅ `config/services.php` - Configuración GIPHY
- ✅ `app/Providers/AppServiceProvider.php` - Repository binding
- ✅ `routes/api.php` - Ruta `/api/v1/media/search`
- ✅ `composer.json` - Guzzle 7.8

---

## 🎯 Endpoint Disponible

**URL:** `GET /api/v1/media/search`

**Autenticación:** Bearer Token (requerida)

**Parámetros:**
- `query` (requerido): Término de búsqueda (1-50 caracteres)
- `limit` (opcional): Número de resultados (1-50, default: 25)
- `offset` (opcional): Paginación (0-4999, default: 0)

**Ejemplo de respuesta:**

```json
{
  "success": true,
  "message": "Media encontrado exitosamente",
  "data": [
    {
      "id": "abc123",
      "title": "Funny Cat GIF",
      "url": "https://giphy.com/gifs/...",
      "rating": "g",
      "username": "gifmaker",
      "images": {
        "original": "https://...",
        "preview": "https://...",
        "mp4": "https://...",
        "webp": "https://..."
      }
    }
  ],
  "pagination": {
    "total_count": 1234,
    "count": 5,
    "offset": 0
  },
  "meta": {
    "status": 200,
    "msg": "OK",
    "response_id": "xyz"
  }
}
```

---

## 🏗️ Arquitectura Hexagonal

```
MediaController → SearchMedia → MediaRepositoryInterface
                                        ↓
                              GiphyMediaRepository
                                        ↓
                                   GIPHY API
```

**Capas implementadas:**
- ✅ **Domain**: Entities, Value Objects, Repository Interface
- ✅ **Application**: Use Cases, DTOs
- ✅ **Infrastructure**: Controllers, Repository Implementation (GIPHY)

**Ventaja:** Puedes agregar fácilmente otros proveedores (Tenor, etc.) implementando la misma interfaz.

---

## 🔮 Extensibilidad

### Agregar otro proveedor (ej: Tenor)

```php
// Crear nueva implementación
class TenorMediaRepository implements MediaRepositoryInterface {
    // Implementar search() y findById()
}

// Cambiar binding en AppServiceProvider
$this->app->bind(
    MediaRepositoryInterface::class,
    TenorMediaRepository::class  // Cambiar aquí
);
```

No necesitas tocar el Domain ni Application, solo cambias la implementación.

---

## ⚠️ Troubleshooting

### Error: "Class 'GuzzleHttp\Client' not found"
```bash
docker-compose exec app composer install
```

### Error: "Unauthorized" de GIPHY
- Verifica tu `GIPHY_API_KEY` en `.env`
- Ejecuta `php artisan config:clear`

### Error: "Unauthenticated"
- Necesitas hacer login primero y obtener un Bearer Token

---

## 📚 Características Implementadas

✅ Búsqueda de media por término  
✅ Paginación (limit + offset)  
✅ Validación en múltiples capas  
✅ Manejo robusto de errores  
✅ Respuestas JSON consistentes  
✅ Autenticación OAuth2 (protegido con `auth:api`)  
✅ Filtro de contenido seguro (rating: g)  
✅ Idioma español configurado  
✅ Múltiples formatos (original, MP4, WebP, preview)  
✅ Logging de errores  
✅ Arquitectura hexagonal desacoplada  
✅ Preparado para múltiples proveedores  

---

## 🎓 Rate Limits de GIPHY

- **Beta Keys**: 100 búsquedas/hora (gratis)
- **Production Keys**: Sin límite (solicita upgrade en Dashboard)

---

## 🚀 ¡Listo para Usar!

Una vez completes los 3 pasos de arriba, tu módulo de Media estará funcionando.

**¡Disfruta buscando GIFs y contenido animado! 🎉**
