# 📡 API Endpoints - Curl Commands

> **Base URL**: `http://localhost:8000/api/v1`

## 📑 Tabla de Contenidos

- [Rutas Públicas](#-rutas-públicas-sin-autenticación)
  - [Health Check](#1-health-check)
  - [Login](#2-login)
- [Rutas Protegidas](#-rutas-protegidas-requieren-autenticación)
  - [Get Authenticated User](#3-get-authenticated-user)
  - [Logout](#4-logout)
  - [Search Media](#5-search-media-giphy)
  - [Get Media by ID](#6-get-media-by-id)
- [Rutas Solo para ADMIN](#-rutas-solo-para-admin)
  - [Register User](#7-register-user-solo-admin)
- [Scripts de Prueba](#-scripts-de-prueba)
- [Casos de Error](#-casos-de-error-comunes)

---

## 🌐 Rutas Públicas (Sin Autenticación)

### 1. Health Check

Verifica que la API esté funcionando correctamente.

**Request:**
```bash
curl -X GET "http://localhost:8000/api/v1/health" \
  -H "Accept: application/json" | jq
```

**Response (200 OK):**
```json
{
  "success": true,
  "status": "healthy",
  "message": "API is running",
  "version": "1.0.0",
  "environment": "local",
  "debug": true,
  "timestamp": "2026-03-19T21:00:00+00:00"
}
```

---

### 2. Login

Inicia sesión y obtiene un token de acceso.

**Request:**
```bash
curl -X POST "http://localhost:8000/api/v1/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "admin@example.com",
    "password": "admin123"
  }' | jq
```

**Request Body:**
```json
{
  "email": "admin@example.com",
  "password": "admin123"
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "name": "Admin",
      "email": "admin@example.com",
      "role": "admin"
    },
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...",
    "token_type": "Bearer"
  }
}
```

**Response (401 Unauthorized):**
```json
{
  "success": false,
  "message": "Invalid credentials"
}
```

---

## 🔒 Rutas Protegidas (Requieren Autenticación)

> **Nota**: Reemplaza `YOUR_TOKEN` con el token obtenido del login.

### 3. Get Authenticated User

Obtiene la información del usuario autenticado.

**Request:**
```bash
curl -X GET "http://localhost:8000/api/v1/user" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json" | jq
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Admin",
    "email": "admin@example.com",
    "role": "admin",
    "created_at": "2026-03-19T21:00:00.000000Z"
  }
}
```

**Response (401 Unauthorized):**
```json
{
  "message": "Unauthenticated."
}
```

---

### 4. Logout

Cierra la sesión revocando el token actual.

**Request:**
```bash
curl -X POST "http://localhost:8000/api/v1/logout" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json" | jq
```

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Logout successful"
}
```

---

### 5. Search Media (GIPHY)

Busca GIFs por término o frase.

**Request:**
```bash
curl -X GET "http://localhost:8000/api/v1/media/search?query=funny+cats&limit=5&offset=0" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json" | jq
```

**Query Parameters:**
- `query` (required, string, max 50): Término de búsqueda
- `limit` (optional, integer, 1-50, default: 25): Cantidad de resultados
- `offset` (optional, integer, 0-4999, default: 0): Offset para paginación

**Ejemplos:**
```bash
# Búsqueda simple
curl -X GET "http://localhost:8000/api/v1/media/search?query=cats" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json" | jq

# Con paginación
curl -X GET "http://localhost:8000/api/v1/media/search?query=dogs&limit=10&offset=20" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json" | jq

# Frase con espacios
curl -X GET "http://localhost:8000/api/v1/media/search?query=funny+animations" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json" | jq
```

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Media encontrado exitosamente",
  "data": [
    {
      "id": "3o6Zt481isNVuQI1l6",
      "title": "Funny Cat GIF",
      "url": "https://giphy.com/gifs/3o6Zt481isNVuQI1l6",
      "rating": "g",
      "username": "catlovers",
      "images": {
        "original": "https://media.giphy.com/media/3o6Zt481isNVuQI1l6/giphy.gif",
        "preview": "https://media.giphy.com/media/3o6Zt481isNVuQI1l6/200.gif",
        "mp4": "https://media.giphy.com/media/3o6Zt481isNVuQI1l6/giphy.mp4",
        "webp": "https://media.giphy.com/media/3o6Zt481isNVuQI1l6/giphy.webp"
      },
      "analytics": null
    }
  ],
  "pagination": {
    "total_count": 1000,
    "count": 5,
    "offset": 0
  },
  "meta": {
    "status": 200,
    "msg": "OK",
    "response_id": "abc123xyz"
  }
}
```

**Response (422 Validation Error):**
```json
{
  "success": false,
  "message": "Error de validación",
  "errors": {
    "query": ["El parámetro query es requerido"],
    "limit": ["El parámetro limit no puede exceder 50"]
  }
}
```

---

### 6. Get Media by ID

Obtiene un GIF específico por su ID.

**Request:**
```bash
curl -X GET "http://localhost:8000/api/v1/media/3o6Zt481isNVuQI1l6" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json" | jq
```

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Media encontrado exitosamente",
  "data": {
    "id": "3o6Zt481isNVuQI1l6",
    "title": "Funny Cat GIF",
    "url": "https://giphy.com/gifs/3o6Zt481isNVuQI1l6",
    "rating": "g",
    "username": "catlovers",
    "images": {
      "original": "https://media.giphy.com/media/3o6Zt481isNVuQI1l6/giphy.gif",
      "preview": "https://media.giphy.com/media/3o6Zt481isNVuQI1l6/200.gif",
      "mp4": "https://media.giphy.com/media/3o6Zt481isNVuQI1l6/giphy.mp4",
      "webp": "https://media.giphy.com/media/3o6Zt481isNVuQI1l6/giphy.webp"
    },
    "analytics": null
  }
}
```

**Response (404 Not Found):**
```json
{
  "success": false,
  "message": "Media no encontrado"
}
```

---

## 🔐 Rutas Solo para ADMIN

> **Nota**: Requieren autenticación Y que el usuario tenga rol `admin`.

### 7. Register User (Solo ADMIN)

Registra un nuevo usuario en el sistema. Solo accesible por usuarios con rol `admin`.

**Request:**
```bash
curl -X POST "http://localhost:8000/api/v1/register" \
  -H "Authorization: Bearer ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "New User",
    "email": "newuser@example.com",
    "password": "password123"
  }' | jq
```

**Request Body:**
```json
{
  "name": "New User",
  "email": "newuser@example.com",
  "password": "password123"
}
```

**Validaciones:**
- `name`: required, string, max 255
- `email`: required, email, max 255, unique
- `password`: required, string, min 6

**Response (201 Created):**
```json
{
  "success": true,
  "message": "User registered successfully",
  "data": {
    "user": {
      "id": 10,
      "name": "New User",
      "email": "newuser@example.com",
      "role": "user",
      "created_at": "2026-03-19 21:00:00"
    },
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...",
    "token_type": "Bearer"
  }
}
```

**Response (403 Forbidden - Usuario no es admin):**
```json
{
  "success": false,
  "message": "Forbidden. Admin privileges required."
}
```

**Response (400 Bad Request - Email ya registrado):**
```json
{
  "success": false,
  "message": "Email already registered"
}
```

---

## 🚀 Scripts de Prueba

### Script Completo de Testing

Guarda este script como `test-api.sh`:

```bash
#!/bin/bash

BASE_URL="http://localhost:8000/api/v1"

echo "🧪 Testing API Endpoints"
echo "========================"
echo ""

# 1. Health Check
echo "1️⃣  Health Check"
curl -s -X GET "$BASE_URL/health" -H "Accept: application/json" | jq
echo ""

# 2. Login (obtener token)
echo "2️⃣  Login as Admin"
LOGIN_RESPONSE=$(curl -s -X POST "$BASE_URL/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "admin@example.com",
    "password": "admin123"
  }')
echo $LOGIN_RESPONSE | jq
TOKEN=$(echo $LOGIN_RESPONSE | jq -r '.data.access_token')
echo ""

# 3. Get Authenticated User
echo "3️⃣  Get Authenticated User"
curl -s -X GET "$BASE_URL/user" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" | jq
echo ""

# 4. Search Media
echo "4️⃣  Search Media (funny cats)"
curl -s -X GET "$BASE_URL/media/search?query=funny+cats&limit=3" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" | jq
echo ""

# 5. Get Media by ID (usa un ID de los resultados anteriores)
echo "5️⃣  Get Media by ID"
MEDIA_ID=$(curl -s -X GET "$BASE_URL/media/search?query=cats&limit=1" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" | jq -r '.data[0].id')

if [ "$MEDIA_ID" != "null" ]; then
  curl -s -X GET "$BASE_URL/media/$MEDIA_ID" \
    -H "Authorization: Bearer $TOKEN" \
    -H "Accept: application/json" | jq
else
  echo "No media found to test"
fi
echo ""

# 6. Register New User (Admin only)
echo "6️⃣  Register New User (as Admin)"
curl -s -X POST "$BASE_URL/register" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Test User",
    "email": "test'$(date +%s)'@example.com",
    "password": "password123"
  }' | jq
echo ""

# 7. Logout
echo "7️⃣  Logout"
curl -s -X POST "$BASE_URL/logout" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" | jq
echo ""

echo "✅ All tests completed!"
```

**Ejecutar:**
```bash
chmod +x test-api.sh
./test-api.sh
```

---

### Variables de Entorno

Para facilitar las pruebas, exporta estas variables:

```bash
# Base URL
export API_BASE_URL="http://localhost:8000/api/v1"

# Credenciales de Admin
export ADMIN_EMAIL="admin@example.com"
export ADMIN_PASSWORD="admin123"

# Obtener y guardar token de admin
export ADMIN_TOKEN=$(curl -s -X POST "$API_BASE_URL/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d "{\"email\":\"$ADMIN_EMAIL\",\"password\":\"$ADMIN_PASSWORD\"}" \
  | jq -r '.data.access_token')

echo "Admin token: $ADMIN_TOKEN"

# Ahora puedes usar $ADMIN_TOKEN en tus comandos
curl -X GET "$API_BASE_URL/user" \
  -H "Authorization: Bearer $ADMIN_TOKEN" \
  -H "Accept: application/json" | jq
```

---

## ❌ Casos de Error Comunes

### Error 401 - No Autenticado

Intentar acceder a una ruta protegida sin token.

**Request:**
```bash
curl -X GET "http://localhost:8000/api/v1/user" \
  -H "Accept: application/json" | jq
```

**Response:**
```json
{
  "message": "Unauthenticated."
}
```

---

### Error 403 - Sin Permisos de Admin

Usuario normal intenta registrar otro usuario.

**Request:**
```bash
curl -X POST "http://localhost:8000/api/v1/register" \
  -H "Authorization: Bearer USER_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Test",
    "email": "test@test.com",
    "password": "pass123"
  }' | jq
```

**Response:**
```json
{
  "success": false,
  "message": "Forbidden. Admin privileges required."
}
```

---

### Error 422 - Validación

Datos inválidos en el request.

**Request:**
```bash
curl -X POST "http://localhost:8000/api/v1/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "invalid-email"
  }' | jq
```

**Response:**
```json
{
  "success": false,
  "message": "Validation error",
  "errors": {
    "email": ["The email field must be a valid email address."],
    "password": ["The password field is required."]
  }
}
```

---

### Error 400 - Email Duplicado

Intentar registrar con un email que ya existe.

**Request:**
```bash
curl -X POST "http://localhost:8000/api/v1/register" \
  -H "Authorization: Bearer ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Duplicate",
    "email": "admin@example.com",
    "password": "pass123"
  }' | jq
```

**Response:**
```json
{
  "success": false,
  "message": "Email already registered"
}
```

---

### Error 503 - Servicio No Disponible

Error al conectar con GIPHY API.

**Response:**
```json
{
  "success": false,
  "message": "Error al conectar con el proveedor de media",
  "error": "Connection timeout"
}
```

---

## 📊 Resumen de Endpoints

| Método | Endpoint | Auth | Admin | Descripción |
|--------|----------|------|-------|-------------|
| GET | `/health` | ❌ | ❌ | Health check |
| POST | `/login` | ❌ | ❌ | Iniciar sesión |
| POST | `/logout` | ✅ | ❌ | Cerrar sesión |
| GET | `/user` | ✅ | ❌ | Usuario autenticado |
| POST | `/register` | ✅ | ✅ | Registrar usuario |
| GET | `/media/search` | ✅ | ❌ | Buscar GIFs |
| GET | `/media/{id}` | ✅ | ❌ | Obtener GIF por ID |

---

## 🔑 Crear Primer Admin

Antes de poder usar los endpoints protegidos, necesitas crear un usuario administrador:

```bash
# Método recomendado: Comando Artisan
docker-compose exec app php artisan create:admin

# Con opciones
docker-compose exec app php artisan create:admin \
  --name="Admin" \
  --email="admin@example.com" \
  --password="admin123"

# Alternativa: Usar el seeder (solo en desarrollo)
docker-compose exec app php artisan db:seed --class=AdminUserSeeder
```

---

## 📝 Notas Importantes

1. **Tokens JWT**: Los tokens tienen una expiración de 1 año por defecto
2. **Rate Limiting**: Considera implementar rate limiting en producción
3. **HTTPS**: Siempre usa HTTPS en producción
4. **CORS**: Configura CORS según tus necesidades
5. **Logs**: Los errores se registran en `storage/logs/laravel.log`

---

## 🌐 Variables de Entorno Requeridas

Asegúrate de tener estas variables en tu `.env`:

```env
# API Base URL
APP_URL=http://localhost:8000

# GIPHY API
GIPHY_API_KEY=your_giphy_api_key_here
GIPHY_BASE_URL=https://api.giphy.com

# Laravel Passport (se generan automáticamente)
# ...claves de cifrado...
```

---

## 🐳 Docker Commands

```bash
# Iniciar servicios
docker-compose up -d

# Ver logs
docker-compose logs -f app

# Ejecutar comandos artisan
docker-compose exec app php artisan [command]

# Acceder al contenedor
docker-compose exec app bash
```

---

**Última actualización**: 2026-03-19
**Versión de la API**: 1.0.0
