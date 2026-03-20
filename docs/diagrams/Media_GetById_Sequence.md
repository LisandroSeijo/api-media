```mermaid
sequenceDiagram
    participant Client as HTTP Client
    participant Controller as GetMediaByIdController
    participant DTO as GetMediaByIdDTO
    participant UseCase as GetMediaById
    participant Repo as MediaRepositoryInterface
    participant Entity as MediaItem
    participant Exception as EntityNotFoundException

    %% === FLUJO PRINCIPAL ===
    Client->>+Controller: GET /api/v1/media/abc123
    
    Note over Controller: Laravel valida que id existe<br/>en la ruta (routing automático)
    
    Controller->>+DTO: new GetMediaByIdDTO(id: "abc123")
    DTO-->>-Controller: dto
    
    Controller->>+UseCase: execute(dto)
    
    Note over UseCase: Buscar en repositorio
    UseCase->>+Repo: findById(dto.id)
    
    Note over Repo: Implementación hace llamada<br/>a API externa (GIPHY)
    Repo->>Repo: GET https://api.giphy.com/v1/gifs/abc123<br/>?api_key=...
    
    alt Media encontrado
        Note over Repo: Transforma respuesta de API
        Repo->>+Entity: MediaItem::fromApiResponse(data)
        Entity-->>-Repo: mediaItem
        Repo-->>UseCase: mediaItem
        
        UseCase->>Entity: toArray()
        Entity-->>UseCase: array con datos del media
        
        UseCase-->>-Controller: result array
        
        Note over Controller: Construye respuesta HTTP
        Controller-->>-Client: 200 JSON Response<br/>{"success": true,<br/>"message": "Media encontrado",<br/>"data": {...}}
    
    else Media no encontrado (null)
        Repo-->>-UseCase: null
        
        Note over UseCase: Media no existe
        UseCase->>+Exception: throw new EntityNotFoundException(<br/>"Media con ID 'abc123' no encontrado")
        Exception-->>-UseCase: exception
        
        UseCase-->>-Controller: EntityNotFoundException
        
        Note over Controller: Captura excepción del dominio
        Controller-->>-Client: 404 JSON Response<br/>{"success": false,<br/>"message": "Media no encontrado",<br/>"error": "Media con ID 'abc123'..."}
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

### 3. Ejecución de Use Case (Application)
- Controller invoca `GetMediaById->execute(dto)`
- Use Case extrae `dto.id`

### 4. Búsqueda en Repository (Domain → Infrastructure)
- Use Case invoca `MediaRepositoryInterface->findById(id)`
- **Implementación concreta** (ej: `GiphyMediaRepository`):
  - Construye URL: `https://api.giphy.com/v1/gifs/{id}`
  - Hace request HTTP a GIPHY API
  - Recibe respuesta JSON

### 5. Transformación de Datos (Infrastructure → Domain)

#### Caso A: Media Encontrado ✅
1. Repository recibe respuesta válida de GIPHY
2. Crea `MediaItem::fromApiResponse(data)`
3. Retorna `MediaItem` al Use Case
4. Use Case invoca `mediaItem->toArray()`
5. Use Case retorna array al Controller
6. Controller construye respuesta `200 OK`

#### Caso B: Media No Encontrado ❌
1. Repository recibe `404` de GIPHY (o response inválido)
2. Repository retorna `null`
3. Use Case detecta `null`
4. Use Case **lanza** `EntityNotFoundException`
5. Controller **captura** la excepción
6. Controller construye respuesta `404 Not Found`

### 6. Respuesta al Cliente (Controller)
- **Éxito (200)**: Retorna datos completos del `MediaItem`
- **No encontrado (404)**: Retorna mensaje de error descriptivo

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