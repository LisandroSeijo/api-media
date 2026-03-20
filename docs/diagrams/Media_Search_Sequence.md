# 🔍 Media Search - Diagrama de Secuencia

Flujo completo del endpoint `GET /api/v1/media/search` con autenticación, validación, llamada a GIPHY API y auditoría.

---

## 🎯 Flujo Exitoso: Búsqueda de GIFs

```mermaid
sequenceDiagram
    autonumber
    
    actor User as 👤 Usuario Autenticado
    participant Router as Laravel Router
    participant AuthMW as Auth Middleware<br/>(auth:api)
    participant Passport as Laravel Passport
    participant Controller as GetMediaSearchController
    participant Request as Laravel Request
    participant UseCase as SearchMedia<br/>(Application)
    participant VO1 as SearchQuery<br/>(Value Object)
    participant VO2 as Limit<br/>(Value Object)
    participant VO3 as Offset<br/>(Value Object)
    participant Repo as GiphyMediaRepository
    participant Guzzle as Guzzle Client
    participant GIPHY as GIPHY API
    participant Entity as MediaItem<br/>(Domain Entity)
    participant EventBus as Event Dispatcher
    participant AuditListener as LogRequestAudited
    participant DB as MySQL

    %% === FASE 1: AUTENTICACIÓN ===
    Note over User,DB: 🔐 FASE 1: AUTENTICACIÓN
    
    User->>Router: GET /api/v1/media/search<br/>?query=cats&limit=5&offset=0<br/>Authorization: Bearer token123
    
    Router->>AuthMW: Check authentication
    AuthMW->>Passport: Verify Bearer token
    Passport->>DB: SELECT * FROM oauth_access_tokens<br/>WHERE id='token123' AND revoked=0
    DB-->>Passport: ✅ Token válido<br/>{user_id: 10, expires_at: ...}
    Passport-->>AuthMW: ✅ User authenticated
    AuthMW->>Request: Inject authenticated user
    AuthMW-->>Router: ✅ Continue
    
    %% === FASE 2: VALIDACIÓN ===
    Note over Router,Controller: ✅ FASE 2: VALIDACIÓN
    
    Router->>Controller: __invoke(Request $request)
    
    Controller->>Request: validate([<br/>  'query' => 'required|string|max:50',<br/>  'limit' => 'nullable|integer|min:1|max:50',<br/>  'offset' => 'nullable|integer|min:0|max:4999'<br/>])
    
    alt Validación Exitosa
        Request-->>Controller: ✅ Validated data:<br/>{query: "cats", limit: "5", offset: "0"}
        
        Controller->>Controller: Cast to int:<br/>limit = (int) "5" = 5<br/>offset = (int) "0" = 0
        
        Controller->>Controller: Create SearchMediaDTO:<br/>{query: "cats", limit: 5, offset: 0}
    else Validación Fallida
        Request-->>Controller: ❌ ValidationException
        Controller-->>User: 422 Unprocessable Entity<br/>{errors: {...}}
        Note over User,Controller: FIN DEL FLUJO
    end
    
    %% === FASE 3: CASO DE USO ===
    Note over Controller,VO3: 🔄 FASE 3: EJECUCIÓN DEL CASO DE USO
    
    Controller->>UseCase: execute(SearchMediaDTO)
    
    UseCase->>VO1: new SearchQuery("cats")
    VO1->>VO1: Validate:<br/>- Not empty ✅<br/>- Max 50 chars ✅
    VO1->>VO1: URL encode: "cats" → "cats"
    VO1-->>UseCase: SearchQuery instance
    
    UseCase->>VO2: new Limit(5)
    VO2->>VO2: Validate:<br/>- Min 1 ✅<br/>- Max 50 ✅
    VO2-->>UseCase: Limit instance
    
    UseCase->>VO3: new Offset(0)
    VO3->>VO3: Validate:<br/>- Min 0 ✅<br/>- Max 4999 ✅
    VO3-->>UseCase: Offset instance
    
    %% === FASE 4: API EXTERNA ===
    Note over UseCase,GIPHY: 🌐 FASE 4: LLAMADA A GIPHY API
    
    UseCase->>Repo: search(SearchQuery, Limit, Offset)
    
    Repo->>Guzzle: Configure client:<br/>- timeout: 10s<br/>- headers: Accept=application/json
    
    Repo->>Guzzle: GET https://api.giphy.com/v1/gifs/search<br/>?api_key=xxx<br/>&q=cats<br/>&limit=5<br/>&offset=0<br/>&rating=g<br/>&lang=es
    
    Guzzle->>GIPHY: HTTP GET Request
    GIPHY-->>Guzzle: 200 OK<br/>Content-Type: application/json<br/>{data: [...], pagination: {...}, meta: {...}}
    Guzzle-->>Repo: Response (PSR-7)
    
    Repo->>Repo: Parse JSON body
    Repo->>Repo: Validate response_id exists<br/>(detectar synthetic responses)
    
    %% === FASE 5: TRANSFORMACIÓN ===
    Note over Repo,Entity: 🔄 FASE 5: TRANSFORMACIÓN A DOMAIN
    
    loop For each item in data
        Repo->>Entity: fromApiResponse(itemData)
        Entity->>Entity: Extract fields:<br/>- id, title, url, rating<br/>- username, images
        Entity-->>Repo: MediaItem instance
    end
    
    Repo->>Repo: Build result array:<br/>{<br/>  data: MediaItem[],<br/>  pagination: {...},<br/>  meta: {...}<br/>}
    
    Repo-->>UseCase: Array with MediaItem[] + metadata
    UseCase-->>Controller: Array with MediaItem[] + metadata
    
    %% === FASE 6: RESPONSE ===
    Note over Controller,User: 📤 FASE 6: JSON RESPONSE
    
    Controller->>Controller: Map entities to arrays
    Controller->>Controller: Build JSON response:<br/>{<br/>  success: true,<br/>  message: "Media encontrado exitosamente",<br/>  data: [...],<br/>  pagination: {...},<br/>  meta: {...}<br/>}
    
    Controller-->>Router: JsonResponse 200
    Router-->>User: ✅ HTTP 200 OK<br/>Content-Type: application/json<br/>[MediaItem array]
    
    %% === FASE 7: AUDITORÍA ===
    Note over Router,DB: 📝 FASE 7: AUDITORÍA (ASÍNCRONA)
    
    Router->>EventBus: Dispatch RequestHandled event<br/>{request: Request, response: JsonResponse}
    
    EventBus->>AuditListener: handle(RequestHandled)
    
    AuditListener->>AuditListener: Check route:<br/>"api/v1/media/search" ≠ "/health" ✅
    
    AuditListener->>AuditListener: Sanitize sensitive data<br/>(no passwords/tokens in this request)
    
    AuditListener->>AuditListener: Extract data:<br/>- user_id: 10<br/>- service: "api/v1/media/search"<br/>- method: "GET"<br/>- request_body: {query: "cats", limit: 5, offset: 0}<br/>- response_code: 200<br/>- response_body: {success: true, ...}<br/>- ip_address: "192.168.1.100"<br/>- user_agent: "curl/7.81.0"
    
    AuditListener->>DB: INSERT INTO audit_logs<br/>(user_id, service, method, ...)<br/>VALUES (10, 'api/v1/media/search', 'GET', ...)
    
    DB-->>AuditListener: ✅ Inserted (id=42)
    
    Note over User,DB: ✅ FLUJO COMPLETADO<br/>✅ Response enviado<br/>✅ Audit log guardado
```

---

## ⚠️ Caso de Error: Validación Fallida (422)

```mermaid
sequenceDiagram
    actor User
    participant Controller
    participant Request
    participant Response
    
    User->>Controller: GET /api/v1/media/search<br/>(sin query parameter)
    
    Controller->>Request: validate(['query' => 'required'])
    Request->>Request: ❌ Validation failed:<br/>query is required
    Request-->>Controller: ValidationException
    
    Controller->>Controller: Catch ValidationException
    Controller->>Response: Build error response:<br/>{<br/>  success: false,<br/>  message: "Error de validación",<br/>  errors: {<br/>    query: ["El parámetro query es requerido"]<br/>  }<br/>}
    
    Response-->>User: ❌ 422 Unprocessable Entity
```

**Casos de validación fallida:**
- Query missing → `422` - "query is required"
- Query too long (>50) → `422` - "query max 50 chars"
- Limit < 1 → `422` - "limit must be at least 1"
- Limit > 50 → `422` - "limit cannot exceed 50"
- Offset < 0 → `422` - "offset must be at least 0"
- Offset > 4999 → `422` - "offset cannot exceed 4999"

---

## ⚠️ Caso de Error: GIPHY API Failure (503)

```mermaid
sequenceDiagram
    participant UseCase
    participant Repo
    participant Guzzle
    participant GIPHY
    participant Controller
    participant User
    participant AuditListener
    participant DB
    
    UseCase->>Repo: search(...)
    Repo->>Guzzle: GET https://api.giphy.com/...
    Guzzle->>GIPHY: HTTP GET
    GIPHY-->>Guzzle: ❌ 500 Internal Server Error
    Guzzle-->>Repo: GuzzleException:<br/>"Server error: 500"
    
    Repo->>Repo: ❌ Catch GuzzleException
    Repo->>Repo: Log error:<br/>Log::error('GIPHY API Error', [...])
    Repo-->>UseCase: ❌ throw RuntimeException:<br/>"Error connecting to GIPHY API: ..."
    
    UseCase-->>Controller: ❌ RuntimeException
    
    Controller->>Controller: Catch RuntimeException
    Controller->>Controller: Build error response:<br/>{<br/>  success: false,<br/>  message: "Error al conectar con el proveedor de media",<br/>  error: "Error connecting to GIPHY API: ..."<br/>}
    
    Controller-->>User: ❌ 503 Service Unavailable
    
    Note over Controller,DB: Audit log registra el error
    Controller->>AuditListener: RequestHandled event
    AuditListener->>DB: INSERT audit_logs<br/>(response_code: 503, response_body: {error: ...})
```

**Errores de GIPHY API:**
- `500 Internal Server Error` → `503` - "GIPHY API failed"
- `Timeout` → `503` - "Connection timeout"
- `Network error` → `503` - "Network error"
- `Synthetic response` (sin response_id) → `503` - "Invalid response"

---

## ⚠️ Caso de Error: Usuario No Autenticado (401)

```mermaid
sequenceDiagram
    actor User
    participant Router
    participant AuthMW
    participant Response
    participant AuditListener
    participant DB
    
    User->>Router: GET /api/v1/media/search?query=cats<br/>(sin Authorization header)
    
    Router->>AuthMW: Check authentication
    AuthMW->>AuthMW: ❌ No Bearer token present
    AuthMW->>Response: AuthenticationException
    
    Response-->>User: ❌ 401 Unauthorized<br/>{message: "Unauthenticated."}
    
    Note over Response,DB: Audit log con user_id = NULL
    Response->>AuditListener: RequestHandled event
    AuditListener->>DB: INSERT audit_logs<br/>(user_id: NULL, response_code: 401)
```

---

## 📊 Detalles Técnicos

### HTTP Request Example

```http
GET /api/v1/media/search?query=funny+cats&limit=10&offset=0 HTTP/1.1
Host: localhost:8000
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...
Accept: application/json
User-Agent: curl/7.81.0
```

### HTTP Response Example (200 OK)

```json
{
  "success": true,
  "message": "Media encontrado exitosamente",
  "data": [
    {
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
    // ... 9 más
  ],
  "pagination": {
    "total_count": 1247,
    "count": 10,
    "offset": 0
  },
  "meta": {
    "status": 200,
    "msg": "OK",
    "response_id": "abc123xyz456"
  }
}
```

### GIPHY API Request

```http
GET /v1/gifs/search?api_key=Q0TgQOqFPpi8t5MJncaxcS9kpGx1ErwD&q=funny+cats&limit=10&offset=0&rating=g&lang=es HTTP/1.1
Host: api.giphy.com
Accept: application/json
```

### Audit Log Entry

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
  'api/v1/media/search',
  'GET',
  '{"query":"funny cats","limit":10,"offset":0}',
  200,
  '{"success":true,"message":"Media encontrado exitosamente","data":[...]}',
  '192.168.1.100',
  'curl/7.81.0',
  '2026-03-20 15:30:45'
);
```

---

## 🔐 Validaciones Aplicadas

### 1. Middleware `auth:api` (Laravel Passport)
- ✅ Bearer token presente
- ✅ Token no revocado
- ✅ Token no expirado
- ✅ Usuario existe

### 2. Request Validation (Laravel Validator)
```php
[
  'query' => 'required|string|max:50',
  'limit' => 'nullable|integer|min:1|max:50',
  'offset' => 'nullable|integer|min:0|max:4999',
]
```

### 3. Value Objects (Domain)
- `SearchQuery`: No vacío, max 50 chars, URL encoded
- `Limit`: Entre 1 y 50 (default 25)
- `Offset`: Entre 0 y 4999 (default 0)

---

## ⏱️ Performance

| Fase | Tiempo Estimado |
|------|-----------------|
| Autenticación | ~10ms (DB query) |
| Validación | ~1ms |
| Value Objects | <1ms |
| GIPHY API Call | ~200-500ms |
| Transformación | ~5ms |
| JSON Response | ~2ms |
| Audit Log | ~5ms (async) |
| **Total** | **~220-520ms** |

---

## 🎯 Principios Demostrados

✅ **Fail Fast** - Validación temprana (auth → request → VO)  
✅ **Error Handling** - Try-catch en cada capa crítica  
✅ **Separation of Concerns** - Cada componente una responsabilidad  
✅ **Dependency Inversion** - UseCase depende de interfaces  
✅ **Event-Driven** - Audit desacoplado vía eventos  
✅ **Type Safety** - Casting explícito de tipos  
✅ **Immutability** - Value Objects readonly  

---

## 🔗 Archivos Relacionados

**Domain:**
- `src/Media/Domain/Entities/MediaItem.php`
- `src/Media/Domain/ValueObjects/SearchQuery.php`
- `src/Media/Domain/ValueObjects/Limit.php`
- `src/Media/Domain/ValueObjects/Offset.php`
- `src/Media/Domain/Repositories/MediaRepositoryInterface.php`

**Application:**
- `src/Media/Application/UseCases/SearchMedia.php`
- `src/Media/Application/DTOs/SearchMediaDTO.php`

**Infrastructure:**
- `src/Media/Infrastructure/Http/Controllers/GetMediaSearchController.php`
- `src/Media/Infrastructure/Persistence/Http/GiphyMediaRepository.php`

**Tests:**
- `tests/Feature/Media/SearchMediaTest.php`
- `tests/E2E/Media/MediaSearchFlowTest.php`
- `tests/E2E/Media/MediaErrorHandlingTest.php`

---

**Última actualización**: 2026-03-20
