# 🎯 Media Get By ID - Diagrama de Secuencia

Flujo completo del endpoint `GET /api/v1/media/{id}` para obtener un GIF específico por su ID.

---

## 🎯 Flujo Exitoso: Obtener GIF por ID

```mermaid
sequenceDiagram
    autonumber
    
    actor User as 👤 Usuario Autenticado
    participant Router as Laravel Router
    participant AuthMW as Auth Middleware<br/>(auth:api)
    participant Passport as Laravel Passport
    participant Controller as GetMediaByIdController
    participant UseCase as GetMediaById<br/>(Application)
    participant Repo as GiphyMediaRepository
    participant Guzzle as Guzzle Client
    participant GIPHY as GIPHY API
    participant Entity as MediaItem<br/>(Domain Entity)
    participant EventBus as Event Dispatcher
    participant AuditListener as LogRequestAudited
    participant DB as MySQL

    %% === FASE 1: AUTENTICACIÓN ===
    Note over User,DB: 🔐 FASE 1: AUTENTICACIÓN
    
    User->>Router: GET /api/v1/media/3o7abKhOpu0NwenH3O<br/>Authorization: Bearer token123
    
    Router->>Router: Extract route parameter:<br/>id = "3o7abKhOpu0NwenH3O"
    
    Router->>AuthMW: Check authentication
    AuthMW->>Passport: Verify Bearer token
    Passport->>DB: SELECT * FROM oauth_access_tokens<br/>WHERE id='token123' AND revoked=0
    DB-->>Passport: ✅ Token válido<br/>{user_id: 10, expires_at: ...}
    Passport-->>AuthMW: ✅ User authenticated
    AuthMW-->>Router: ✅ Continue
    
    %% === FASE 2: VALIDACIÓN ===
    Note over Router,Controller: ✅ FASE 2: VALIDACIÓN
    
    Router->>Controller: __invoke(Request $request, string $id)<br/>$id = "3o7abKhOpu0NwenH3O"
    
    Note over Controller: Laravel garantiza que $id existe<br/>(route binding obligatorio)
    
    Controller->>Controller: Create GetMediaByIdDTO:<br/>{id: "3o7abKhOpu0NwenH3O"}
    
    %% === FASE 3: CASO DE USO ===
    Note over Controller,UseCase: 🔄 FASE 3: EJECUCIÓN DEL CASO DE USO
    
    Controller->>UseCase: execute(GetMediaByIdDTO)
    
    UseCase->>UseCase: Extract ID from DTO:<br/>id = "3o7abKhOpu0NwenH3O"
    
    %% === FASE 4: API EXTERNA ===
    Note over UseCase,GIPHY: 🌐 FASE 4: LLAMADA A GIPHY API
    
    UseCase->>Repo: findById("3o7abKhOpu0NwenH3O")
    
    Repo->>Guzzle: Configure client:<br/>- timeout: 10s<br/>- headers: Accept=application/json
    
    Repo->>Guzzle: GET https://api.giphy.com/v1/gifs/3o7abKhOpu0NwenH3O<br/>?api_key=xxx
    
    Guzzle->>GIPHY: HTTP GET Request
    
    alt GIF Encontrado
        GIPHY-->>Guzzle: ✅ 200 OK<br/>{data: {id, title, url, ...}, meta: {...}}
        Guzzle-->>Repo: Response (PSR-7)
        
        Repo->>Repo: Parse JSON body
        Repo->>Repo: Check if data exists
        
        %% === FASE 5: TRANSFORMACIÓN ===
        Note over Repo,Entity: 🔄 FASE 5: TRANSFORMACIÓN A DOMAIN
        
        Repo->>Entity: fromApiResponse(data)
        Entity->>Entity: Extract fields:<br/>- id: "3o7abKhOpu0NwenH3O"<br/>- title: "Funny Cat GIF"<br/>- url: "https://giphy.com/gifs/..."<br/>- rating: "g"<br/>- username: "studios"<br/>- images: {...}
        Entity-->>Repo: MediaItem instance
        
        Repo-->>UseCase: MediaItem
        UseCase-->>Controller: MediaItem
        
        %% === FASE 6: RESPONSE ===
        Note over Controller,User: 📤 FASE 6: JSON RESPONSE
        
        Controller->>Controller: Map entity to array
        Controller->>Controller: Build JSON response:<br/>{<br/>  success: true,<br/>  message: "Media encontrado exitosamente",<br/>  data: {<br/>    id: "3o7abKhOpu0NwenH3O",<br/>    title: "Funny Cat GIF",<br/>    ...<br/>  }<br/>}
        
        Controller-->>Router: JsonResponse 200
        Router-->>User: ✅ HTTP 200 OK<br/>Content-Type: application/json<br/>{MediaItem}
        
    else GIF No Encontrado
        GIPHY-->>Guzzle: ❌ 404 Not Found
        Guzzle-->>Repo: GuzzleException (404)
        Repo->>Repo: Catch GuzzleException
        Repo->>Repo: Log error
        Repo-->>UseCase: return null
        UseCase->>UseCase: Check: mediaItem === null
        UseCase-->>Controller: ❌ throw EntityNotFoundException:<br/>"Entity MediaItem with ID ... not found"
        
        Controller->>Controller: Catch EntityNotFoundException
        Controller->>Controller: Build error response:<br/>{<br/>  success: false,<br/>  message: "Media no encontrado",<br/>  error: "Entity MediaItem with ID ... not found"<br/>}
        
        Controller-->>User: ❌ 404 Not Found
    end
    
    %% === FASE 7: AUDITORÍA ===
    Note over Router,DB: 📝 FASE 7: AUDITORÍA (ASÍNCRONA)
    
    Router->>EventBus: Dispatch RequestHandled event
    EventBus->>AuditListener: handle(RequestHandled)
    
    AuditListener->>AuditListener: Check route:<br/>"api/v1/media/3o7abKhOpu0NwenH3O" ≠ "/health" ✅
    
    AuditListener->>AuditListener: Extract data:<br/>- user_id: 10<br/>- service: "api/v1/media/3o7abKhOpu0NwenH3O"<br/>- method: "GET"<br/>- request_body: {id: "3o7abKhOpu0NwenH3O"}<br/>- response_code: 200 (or 404)<br/>- response_body: {...}<br/>- ip_address: "192.168.1.100"
    
    AuditListener->>DB: INSERT INTO audit_logs
    DB-->>AuditListener: ✅ Inserted
    
    Note over User,DB: ✅ FLUJO COMPLETADO
```

---

## ⚠️ Caso de Error: GIF No Encontrado (404)

```mermaid
sequenceDiagram
    participant Controller
    participant UseCase
    participant Repo
    participant GIPHY
    participant User
    participant AuditListener
    participant DB
    
    Controller->>UseCase: execute(GetMediaByIdDTO)
    UseCase->>Repo: findById("invalid_id_xxx")
    
    Repo->>GIPHY: GET /v1/gifs/invalid_id_xxx
    GIPHY-->>Repo: ❌ 404 Not Found<br/>{message: "Not Found"}
    
    Repo->>Repo: ❌ Catch GuzzleException (404)
    Repo->>Repo: Log::error('GIPHY API Error (Find by ID)', [...])
    Repo-->>UseCase: return null
    
    UseCase->>UseCase: ❌ Check: mediaItem === null
    UseCase-->>Controller: ❌ throw EntityNotFoundException:<br/>"Entity MediaItem with ID 'invalid_id_xxx' not found"
    
    Controller->>Controller: Catch EntityNotFoundException
    Controller->>Controller: Build error response:<br/>{<br/>  success: false,<br/>  message: "Media no encontrado",<br/>  error: "Entity MediaItem with ID 'invalid_id_xxx' not found"<br/>}
    
    Controller-->>User: ❌ 404 Not Found
    
    Note over Controller,DB: Audit log registra 404
    Controller->>AuditListener: RequestHandled event
    AuditListener->>DB: INSERT audit_logs<br/>(response_code: 404)
```

---

## ⚠️ Caso de Error: GIPHY API Error (503)

```mermaid
sequenceDiagram
    participant UseCase
    participant Repo
    participant GIPHY
    participant Controller
    participant User
    
    UseCase->>Repo: findById("abc123")
    Repo->>GIPHY: GET /v1/gifs/abc123
    GIPHY-->>Repo: ❌ 500 Internal Server Error
    
    Repo->>Repo: ❌ Catch GuzzleException
    Repo->>Repo: Log error
    Repo-->>UseCase: return null
    
    UseCase->>UseCase: ❌ Check: mediaItem === null
    UseCase-->>Controller: ❌ throw EntityNotFoundException
    
    Controller->>Controller: Catch EntityNotFoundException
    Controller->>Controller: Build error response:<br/>{<br/>  success: false,<br/>  message: "Media no encontrado",<br/>  error: "Entity MediaItem with ID 'abc123' not found"<br/>}
    
    Controller-->>User: ❌ 404 Not Found
```

**Nota**: Actualmente, tanto 404 de GIPHY como errores 500 de GIPHY retornan `null` en el repository, lo que resulta en `EntityNotFoundException` (404) al cliente. Esto es intencional para:

---

## ⚠️ Caso de Error: ID Inválido (Formato)

Laravel acepta cualquier string en `{id}`, por lo que IDs malformados llegarán al controller:

```mermaid
sequenceDiagram
    participant User
    participant Router
    participant Controller
    participant UseCase
    participant Repo
    participant GIPHY
    
    User->>Router: GET /api/v1/media/<script>alert(1)</script>
    Router->>Router: Extract {id} = "<script>alert(1)</script>"
    Router->>Controller: __invoke(..., "<script>alert(1)</script>")
    Controller->>UseCase: execute({id: "<script>alert(1)</script>"})
    UseCase->>Repo: findById("<script>alert(1)</script>")
    Repo->>GIPHY: GET /v1/gifs/<script>alert(1)</script>
    GIPHY-->>Repo: ❌ 404 Not Found
    Repo-->>UseCase: null
    UseCase-->>Controller: null
    Controller-->>User: ❌ 404 Not Found
```

**Seguridad**: No hay vulnerabilidad XSS porque:
1. El ID nunca se renderiza en HTML
2. Laravel escapa JSON responses automáticamente
3. El repositorio solo hace HTTP request (GIPHY valida)

---

## 📊 Detalles Técnicos

### HTTP Request Example

```http
GET /api/v1/media/3o7abKhOpu0NwenH3O HTTP/1.1
Host: localhost:8000
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...
Accept: application/json
User-Agent: PostmanRuntime/7.29.0
```

### HTTP Response Example (200 OK)

```json
{
  "success": true,
  "message": "Media encontrado exitosamente",
  "data": {
    "id": "3o7abKhOpu0NwenH3O",
    "title": "Funny Cat GIF by GIPHY Studios Originals",
    "url": "https://giphy.com/gifs/3o7abKhOpu0NwenH3O",
    "rating": "g",
    "username": "studios",
    "images": {
      "original": {
        "url": "https://media.giphy.com/media/3o7abKhOpu0NwenH3O/giphy.gif"
      },
      "preview_gif": {
        "url": "https://media.giphy.com/media/3o7abKhOpu0NwenH3O/200.gif"
      }
    }
  }
}
```

### HTTP Response Example (404 Not Found)

```json
{
  "success": false,
  "message": "Media no encontrado",
  "error": "No media found with ID: invalid_id_xxx"
}
```

### GIPHY API Request

```http
GET /v1/gifs/3o7abKhOpu0NwenH3O?api_key=Q0TgQOqFPpi8t5MJncaxcS9kpGx1ErwD HTTP/1.1
Host: api.giphy.com
Accept: application/json
```

### GIPHY API Response (200 OK)

```json
{
  "data": {
    "id": "3o7abKhOpu0NwenH3O",
    "title": "Funny Cat GIF by GIPHY Studios Originals",
    "url": "https://giphy.com/gifs/3o7abKhOpu0NwenH3O",
    "rating": "g",
    "username": "studios",
    "images": {
      "original": {
        "url": "https://media.giphy.com/media/3o7abKhOpu0NwenH3O/giphy.gif",
        "width": "480",
        "height": "270"
      },
      "preview_gif": {
        "url": "https://media.giphy.com/media/3o7abKhOpu0NwenH3O/200.gif",
        "width": "200",
        "height": "113"
      }
    }
  },
  "meta": {
    "status": 200,
    "msg": "OK",
    "response_id": "xyz789abc"
  }
}
```

### Audit Log Entry (Success)

```sql
INSERT INTO audit_logs (
  user_id,
  service,
  method,
  request_body,
  response_code,
  response_body,
  ip_address,
  user_agent,
  created_at
) VALUES (
  10,
  'api/v1/media/3o7abKhOpu0NwenH3O',
  'GET',
  '{"id":"3o7abKhOpu0NwenH3O"}',
  200,
  '{"success":true,"message":"Media encontrado exitosamente","data":{...}}',
  '192.168.1.100',
  'PostmanRuntime/7.29.0',
  '2026-03-20 15:35:20'
);
```

### Audit Log Entry (Not Found)

```sql
INSERT INTO audit_logs (
  user_id,
  service,
  method,
  request_body,
  response_code,
  response_body,
  ip_address,
  user_agent,
  created_at
) VALUES (
  10,
  'api/v1/media/invalid_id_xxx',
  'GET',
  '{"id":"invalid_id_xxx"}',
  404,
  '{"success":false,"message":"Media no encontrado","error":"No media found with ID: invalid_id_xxx"}',
  '192.168.1.100',
  'PostmanRuntime/7.29.0',
  '2026-03-20 15:36:45'
);
```

---

## 🔐 Validaciones Aplicadas

### 1. Middleware `auth:api` (Laravel Passport)
- ✅ Bearer token presente
- ✅ Token no revocado
- ✅ Token no expirado
- ✅ Usuario existe

### 2. Route Parameter Binding
```php
Route::get('/media/{id}', GetMediaByIdController::class);
```
- Laravel extrae `{id}` automáticamente
- Si no hay ID en la URL, retorna 404 (ruta no encontrada)
- No requiere validación adicional en el controller

### 3. DTO Creation
```php
$dto = new GetMediaByIdDTO(id: $id);
```
- Simple asignación, sin validaciones complejas
- Retorna `MediaItem` si encontrado
- Retorna `null` si no encontrado o error

---

## ⏱️ Performance

| Fase | Tiempo Estimado |
|------|-----------------|
| Autenticación | ~10ms (DB query) |
| Routing | ~1ms |
| GIPHY API Call (by ID) | ~150-300ms |
| Transformación | ~2ms |
| JSON Response | ~1ms |
| Audit Log | ~5ms (async) |
| **Total** | **~170-320ms** |

**Nota**: Más rápido que `/search` porque:
- No hay paginación
- No hay múltiples items
- GIPHY responde más rápido en queries por ID

---

## 🔄 Diferencias con `/search`

| Aspecto | `/search` | `/{id}` |
|---------|-----------|---------|
| Query params | query, limit, offset | ninguno |
| Value Objects | 3 (SearchQuery, Limit, Offset) | 0 |
| Validation complexity | Alta | Baja |
| GIPHY endpoint | `/v1/gifs/search` | `/v1/gifs/{id}` |
| Response | Array de items | Item único |
| Typical response time | 200-500ms | 150-300ms |
| Cache opportunity | Baja (queries dinámicos) | Alta (IDs estáticos) |

---

## 💡 Posibles Mejoras

### 1. Caché de Resultados
```php
// En GiphyMediaRepository::findById()
$cached = Cache::remember(
    "media:giphy:{$id}",
    now()->addHours(24),
    fn() => $this->fetchFromGiphy($id)
);
```

**Ventajas:**
- ✅ Reduce llamadas a GIPHY API
- ✅ Mejora performance (cache hit ~2ms vs API call ~200ms)
- ✅ Reduce costos (GIPHY tiene rate limits)

**Desventajas:**
- ❌ Datos pueden quedar desactualizados
- ❌ Requiere gestión de invalidación

### 2. Validación de Formato de ID

Agregar validación en Controller:

```php
// GetMediaByIdController::__invoke()
$validated = $request->validate([
    'id' => [
        'required',
        'string',
        'max:100',
        'regex:/^[a-zA-Z0-9]+$/' // Solo alfanuméricos
    ]
]);
```

**Ventajas:**
- ✅ Previene llamadas a GIPHY con IDs obviamente inválidos
- ✅ Fail fast
- ✅ Mejor logging

### 3. Retornar 503 en vez de 404 para Errores de API

Diferenciar entre "no encontrado" y "error de API":

```php
// GiphyMediaRepository::findById()
catch (ClientException $e) {
    if ($e->getCode() === 404) {
        return null; // Not found → Controller retorna 404
    }
    throw new RuntimeException('GIPHY API error'); // Controller retorna 503
}
```

---

## 🎯 Principios Demostrados

✅ **Simplicity** - Flujo más simple que `/search`  
✅ **Domain Exceptions** - `EntityNotFoundException` en Domain layer  
✅ **Explicit is Better** - Excepción clara en vez de null  
✅ **Error Handling** - Catch específico por tipo de error  
✅ **Separation of Concerns** - Repository maneja detalles de GIPHY  
✅ **Consistent API** - Mismo formato de response que `/search`  
✅ **Event-Driven** - Audit desacoplado  

---

## 🔗 Archivos Relacionados

**Domain:**
- `src/Media/Domain/Entities/MediaItem.php`
- `src/Media/Domain/Repositories/MediaRepositoryInterface.php`

**Application:**
- `src/Media/Application/UseCases/GetMediaById.php`
- `src/Media/Application/DTOs/GetMediaByIdDTO.php`

**Infrastructure:**
- `src/Media/Infrastructure/Http/Controllers/GetMediaByIdController.php`
- `src/Media/Infrastructure/Persistence/Http/GiphyMediaRepository.php`

**Routes:**
- `routes/api.php` - `Route::get('/media/{id}', GetMediaByIdController::class)`

**Tests:**
- `tests/E2E/Media/MediaSearchFlowTest.php` - `test_complete_media_search_flow()`

---

## 🧪 Testing

### Test Case: GIF Encontrado

```php
public function test_get_media_by_id_returns_success(): void
{
    $mock = $this->createGiphyMock([
        ['body' => $this->createGiphyByIdResponse('abc123')],
    ]);
    $this->bindGiphyMock($mock);

    $auth = $this->loginAsUser();

    $response = $this->getJson('/api/v1/media/abc123', [
        'Authorization' => 'Bearer ' . $auth['token'],
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => ['id' => 'abc123'],
        ]);
}
```

### Test Case: GIF No Encontrado

```php
public function test_get_media_by_id_returns_404_when_not_found(): void
{
    $mock = new MockHandler([
        new Response(404, [], json_encode(['message' => 'Not Found'])),
    ]);
    $this->bindGiphyMock(new Client(['handler' => HandlerStack::create($mock)]));

    $auth = $this->loginAsUser();

    $response = $this->getJson('/api/v1/media/invalid_id', [
        'Authorization' => 'Bearer ' . $auth['token'],
    ]);

    $response->assertStatus(404)
        ->assertJson([
            'success' => false,
            'message' => 'Media no encontrado',
        ]);
}
```

---

**Última actualización**: 2026-03-20
