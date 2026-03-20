```mermaid
sequenceDiagram
    participant Client as HTTP Client
    participant Controller as GetMediaByIdController
    participant DTO as GetMediaByIdDTO
    participant UseCase as GetMediaById
    participant Cache as CacheServiceInterface
    participant Repo as MediaRepositoryInterface
    participant Entity as MediaItem
    participant Exception as EntityNotFoundException

    %% === FLUJO PRINCIPAL ===
    Client->>+Controller: GET /api/v1/media/abc123
    
    Note over Controller: Laravel valida que id existe<br/>en la ruta (routing automático)
    
    Controller->>+DTO: new GetMediaByIdDTO(id: "abc123")
    DTO-->>-Controller: dto
    
    Controller->>+UseCase: execute(dto)
    
    Note over UseCase: Generar cache key
    UseCase->>UseCase: cacheKey = "media:id:abc123"
    
    %% === CACHE HIT ===
    alt Cache habilitado
        UseCase->>+Cache: has(cacheKey)
        Cache-->>-UseCase: true (cache hit)
        
        UseCase->>+Cache: get(cacheKey)
        Cache-->>-UseCase: cached data (array)
        
        Note over UseCase: Reconstruir entidad desde cache
        UseCase->>+Entity: MediaItem::fromApiResponse(cachedData)
        Entity-->>-UseCase: mediaItem
        
        UseCase-->>-Controller: mediaItem
        
        Note over Controller: Construye respuesta HTTP
        Controller-->>-Client: 200 JSON Response<br/>{"success": true,<br/>"message": "Media encontrado",<br/>"data": {...}}<br/>⚡ FROM CACHE (~5ms)
    
    else Cache miss o deshabilitado
        Note over UseCase: Cache miss, buscar en repositorio
        UseCase->>+Repo: findById(dto.id)
        
        Note over Repo: Implementación hace llamada<br/>a API externa (GIPHY)
        Repo->>Repo: GET https://api.giphy.com/v1/gifs/abc123<br/>?api_key=...
        
        alt Media encontrado
            Note over Repo: Transforma respuesta de API
            Repo->>+Entity: MediaItem::fromApiResponse(data)
            Entity-->>-Repo: mediaItem
            Repo-->>-UseCase: mediaItem
            
            Note over UseCase: Guardar en cache
            alt Cache habilitado
                UseCase->>Entity: toArray()
                Entity-->>UseCase: array
                UseCase->>+Cache: put(cacheKey, array, ttlMinutes)
                Cache-->>-UseCase: void
            end
            
            UseCase-->>-Controller: mediaItem
            
            Note over Controller: Construye respuesta HTTP
            Controller-->>-Client: 200 JSON Response<br/>{"success": true,<br/>"message": "Media encontrado",<br/>"data": {...}}<br/>⏱️ FROM API (~100-300ms)
        
        else Media no encontrado (null)
            Repo-->>-UseCase: null
            
            Note over UseCase: Media no existe
            UseCase->>+Exception: throw new EntityNotFoundException(<br/>"Media con ID 'abc123' no encontrado")
            Exception-->>-UseCase: exception
            
            UseCase-->>-Controller: EntityNotFoundException
            
            Note over Controller: Captura excepción del dominio
            Controller-->>-Client: 404 JSON Response<br/>{"success": false,<br/>"message": "Media no encontrado",<br/>"error": "Media con ID 'abc123'..."}
        end
    end
```

## Flujo de Eventos

### 1. Recepción de Request (Controller)
- Cliente hace GET a `/api/v1/media/{id}`
- Laravel routing extrae `{id}` de la URL
- Controller recibe el `id` como parámetro del método `__invoke()`

### 2. Creación de DTO (Application)
- Controller crea `GetMediaByIdDTO` con el `id` recibido
- **No hay validación explícita**: Laravel garantiza que `id` existe si la ruta matcheó

### 3. Ejecución de Use Case con Cache (Application)
- Controller invoca `GetMediaById->execute(dto)`
- Use Case genera cache key: `media:id:{id}`
- **Cache Hit** ⚡:
  - Si cache habilitado y key existe
  - Recupera datos desde Redis (<5ms)
  - Reconstruye `MediaItem` desde array cacheado
  - Retorna inmediatamente
- **Cache Miss** ⏱️:
  - Continúa al paso 4 (búsqueda en Repository)

### 4. Búsqueda en Repository (Domain → Infrastructure)
- Use Case invoca `MediaRepositoryInterface->findById(id)`
- **Implementación concreta** (ej: `GiphyMediaRepository`):
  - Construye URL: `https://api.giphy.com/v1/gifs/{id}`
  - Hace request HTTP a GIPHY API (~100-300ms)
  - Recibe respuesta JSON

### 5. Transformación de Datos (Infrastructure → Domain)

#### Caso A: Media Encontrado ✅
1. Repository recibe respuesta válida de GIPHY
2. Crea `MediaItem::fromApiResponse(data)`
3. Retorna `MediaItem` al Use Case
4. **Use Case guarda en cache**:
   - Si cache habilitado
   - Convierte `mediaItem->toArray()`
   - Almacena en Redis con TTL configurable
5. Use Case retorna `MediaItem` al Controller
6. Controller construye respuesta `200 OK`

#### Caso B: Media No Encontrado ❌
1. Repository recibe `404` de GIPHY (o response inválido)
2. Repository retorna `null`
3. Use Case detecta `null`
4. Use Case **lanza** `EntityNotFoundException`
5. Controller **captura** la excepción
6. Controller construye respuesta `404 Not Found`
7. **No se cachea** el resultado negativo

### 6. Respuesta al Cliente (Controller)
- **Éxito (200)**: Retorna datos completos del `MediaItem`
  - ⚡ Cache Hit: ~5ms
  - ⏱️ Cache Miss: ~100-300ms
- **No encontrado (404)**: Retorna mensaje de error descriptivo

## Beneficios del Cache

### Performance
- **Primera llamada**: ~100-300ms (llamada a GIPHY API + guardado en cache)
- **Llamadas subsecuentes**: ~5ms (lectura de Redis)
- **Mejora**: 20-60x más rápido

### Reducción de Costos
- Menos llamadas a GIPHY API
- Menor latencia para usuarios
- Menor carga en servicios externos

### Configuración
- TTL configurable vía `.env` (`MEDIA_CACHE_TTL_MINUTES`)
- Cache puede deshabilitarse sin cambiar código (`MEDIA_CACHE_ENABLED=false`)
- Invalidación automática después del TTL

## Casos de Uso

### Éxito (200)
```json
{
  "success": true,
  "message": "Media encontrado exitosamente",
  "data": {
    "id": "abc123",
    "title": "Funny Cat GIF",
    "url": "https://giphy.com/gifs/abc123",
    "rating": "g",
    "username": "catlovers",
    "images": {
      "original": {
        "url": "https://media.giphy.com/media/abc123/giphy.gif"
      },
      "preview_gif": {
        "url": "https://media.giphy.com/media/abc123/200.gif"
      }
    }
  }
}
```