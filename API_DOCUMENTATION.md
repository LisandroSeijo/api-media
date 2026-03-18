# 📚 API REST con OAuth2.0 - Documentación

## 🚀 Descripción

API REST desarrollada con Laravel 13 y autenticación OAuth2.0 usando Laravel Passport.

## 🔗 URL Base

```
http://localhost:8000/api/v1
```

## 🔐 Autenticación

Esta API utiliza OAuth2.0 mediante Laravel Passport. Para acceder a los endpoints protegidos, debes incluir el token de acceso en el header de autorización:

```
Authorization: Bearer {access_token}
```

---

## 📋 Endpoints

### 🟢 Endpoints Públicos (Sin autenticación)

#### 1. Health Check

Verifica que la API está funcionando correctamente.

**Endpoint:** `GET /health`

**Request:**
```bash
curl -X GET http://localhost:8000/api/v1/health
```

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "API is running",
  "version": "1.0.0",
  "timestamp": "2026-03-18T03:30:00.000000Z"
}
```

---

#### 2. Registrar Usuario

Registra un nuevo usuario en el sistema.

**Endpoint:** `POST /register`

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Request Ejemplo:**
```bash
curl -X POST http://localhost:8000/api/v1/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

**Response:** `201 Created`
```json
{
  "success": true,
  "message": "User registered successfully",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "created_at": "2026-03-18T03:30:00.000000Z",
      "updated_at": "2026-03-18T03:30:00.000000Z"
    },
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "token_type": "Bearer"
  }
}
```

**Validaciones:**
- `name`: requerido, string, máximo 255 caracteres
- `email`: requerido, email válido, único en la base de datos
- `password`: requerido, mínimo 8 caracteres, debe coincidir con password_confirmation

---

#### 3. Iniciar Sesión

Autentica un usuario y devuelve un token de acceso.

**Endpoint:** `POST /login`

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Body:**
```json
{
  "email": "john@example.com",
  "password": "password123"
}
```

**Request Ejemplo:**
```bash
curl -X POST http://localhost:8000/api/v1/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password123"
  }'
```

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "created_at": "2026-03-18T03:30:00.000000Z",
      "updated_at": "2026-03-18T03:30:00.000000Z"
    },
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "token_type": "Bearer"
  }
}
```

**Error Response:** `401 Unauthorized`
```json
{
  "success": false,
  "message": "Invalid credentials"
}
```

---

### 🔒 Endpoints Protegidos (Requieren autenticación)

Para todos los endpoints protegidos, debes incluir el token en el header:

```
Authorization: Bearer {tu_token_de_acceso}
```

---

#### 4. Cerrar Sesión

Revoca el token de acceso actual.

**Endpoint:** `POST /logout`

**Headers:**
```
Authorization: Bearer {access_token}
Accept: application/json
```

**Request Ejemplo:**
```bash
curl -X POST http://localhost:8000/api/v1/logout \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc..." \
  -H "Accept: application/json"
```

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Logout successful"
}
```

---

#### 5. Obtener Usuario Autenticado

Obtiene los datos del usuario actualmente autenticado.

**Endpoint:** `GET /user`

**Headers:**
```
Authorization: Bearer {access_token}
Accept: application/json
```

**Request Ejemplo:**
```bash
curl -X GET http://localhost:8000/api/v1/user \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc..." \
  -H "Accept: application/json"
```

**Response:** `200 OK`
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "created_at": "2026-03-18T03:30:00.000000Z",
    "updated_at": "2026-03-18T03:30:00.000000Z"
  }
}
```

---

#### 6. Listar Posts

Obtiene una lista de todos los posts.

**Endpoint:** `GET /posts`

**Headers:**
```
Authorization: Bearer {access_token}
Accept: application/json
```

**Request Ejemplo:**
```bash
curl -X GET http://localhost:8000/api/v1/posts \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc..." \
  -H "Accept: application/json"
```

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Posts retrieved successfully",
  "data": [
    {
      "id": 1,
      "title": "First Post",
      "content": "This is the content of the first post",
      "author": "John Doe",
      "created_at": "2026-03-16T03:30:00.000000Z"
    },
    {
      "id": 2,
      "title": "Second Post",
      "content": "This is the content of the second post",
      "author": "John Doe",
      "created_at": "2026-03-17T03:30:00.000000Z"
    }
  ]
}
```

---

#### 7. Crear Post

Crea un nuevo post.

**Endpoint:** `POST /posts`

**Headers:**
```
Authorization: Bearer {access_token}
Content-Type: application/json
Accept: application/json
```

**Body:**
```json
{
  "title": "Mi Nuevo Post",
  "content": "Este es el contenido de mi nuevo post"
}
```

**Request Ejemplo:**
```bash
curl -X POST http://localhost:8000/api/v1/posts \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc..." \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "title": "Mi Nuevo Post",
    "content": "Este es el contenido de mi nuevo post"
  }'
```

**Response:** `201 Created`
```json
{
  "success": true,
  "message": "Post created successfully",
  "data": {
    "id": 4,
    "title": "Mi Nuevo Post",
    "content": "Este es el contenido de mi nuevo post",
    "author": "John Doe",
    "created_at": "2026-03-18T03:30:00.000000Z"
  }
}
```

**Validaciones:**
- `title`: requerido, string, máximo 255 caracteres
- `content`: requerido, string

---

#### 8. Obtener Post Específico

Obtiene los detalles de un post específico.

**Endpoint:** `GET /posts/{id}`

**Headers:**
```
Authorization: Bearer {access_token}
Accept: application/json
```

**Request Ejemplo:**
```bash
curl -X GET http://localhost:8000/api/v1/posts/1 \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc..." \
  -H "Accept: application/json"
```

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Post retrieved successfully",
  "data": {
    "id": 1,
    "title": "Post Title",
    "content": "This is the content of the post",
    "author": "John Doe",
    "created_at": "2026-03-15T03:30:00.000000Z"
  }
}
```

---

#### 9. Actualizar Post

Actualiza un post existente.

**Endpoint:** `PUT /posts/{id}` o `PATCH /posts/{id}`

**Headers:**
```
Authorization: Bearer {access_token}
Content-Type: application/json
Accept: application/json
```

**Body:**
```json
{
  "title": "Título Actualizado",
  "content": "Contenido actualizado del post"
}
```

**Request Ejemplo:**
```bash
curl -X PUT http://localhost:8000/api/v1/posts/1 \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc..." \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "title": "Título Actualizado",
    "content": "Contenido actualizado del post"
  }'
```

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Post updated successfully",
  "data": {
    "id": 1,
    "title": "Título Actualizado",
    "content": "Contenido actualizado del post",
    "author": "John Doe",
    "updated_at": "2026-03-18T03:30:00.000000Z"
  }
}
```

---

#### 10. Eliminar Post

Elimina un post existente.

**Endpoint:** `DELETE /posts/{id}`

**Headers:**
```
Authorization: Bearer {access_token}
Accept: application/json
```

**Request Ejemplo:**
```bash
curl -X DELETE http://localhost:8000/api/v1/posts/1 \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc..." \
  -H "Accept: application/json"
```

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Post deleted successfully",
  "data": {
    "id": 1
  }
}
```

---

## ⚠️ Códigos de Estado HTTP

| Código | Descripción |
|--------|-------------|
| 200 | OK - Solicitud exitosa |
| 201 | Created - Recurso creado exitosamente |
| 401 | Unauthorized - Token inválido o ausente |
| 422 | Unprocessable Entity - Error de validación |
| 500 | Internal Server Error - Error del servidor |

---

## 🔧 Respuestas de Error

### Error de Validación (422)
```json
{
  "success": false,
  "message": "Validation error",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password must be at least 8 characters."]
  }
}
```

### Error de Autenticación (401)
```json
{
  "success": false,
  "message": "Unauthenticated."
}
```

### Error del Servidor (500)
```json
{
  "success": false,
  "message": "Failed to perform action",
  "error": "Error message details"
}
```

---

## 🧪 Probar la API

### Con cURL

1. **Registrar un usuario:**
```bash
curl -X POST http://localhost:8000/api/v1/register \
  -H "Content-Type: application/json" \
  -d '{"name":"Test User","email":"test@example.com","password":"password123","password_confirmation":"password123"}'
```

2. **Guardar el token de la respuesta y usarlo:**
```bash
TOKEN="tu_token_aqui"

curl -X GET http://localhost:8000/api/v1/user \
  -H "Authorization: Bearer $TOKEN"
```

### Con Postman

1. Importa los endpoints en Postman
2. En la pestaña "Authorization", selecciona "Bearer Token"
3. Pega tu token de acceso
4. Realiza las peticiones

### Con Thunder Client (VS Code)

1. Instala la extensión Thunder Client
2. Crea una nueva colección
3. Agrega los endpoints
4. Configura el Bearer Token en la sección Auth

---

## 📝 Notas Importantes

1. **Tokens**: Los tokens de acceso no expiran por defecto, pero puedes configurar la expiración en `config/passport.php`

2. **CORS**: Si necesitas acceder desde un frontend en otro dominio, configura CORS en `config/cors.php`

3. **Rate Limiting**: Laravel incluye rate limiting por defecto. Puedes configurarlo en `app/Http/Kernel.php`

4. **Ambiente de Desarrollo**: Esta configuración es para desarrollo. En producción, asegúrate de:
   - Usar HTTPS
   - Configurar variables de entorno apropiadas
   - Implementar rate limiting más estricto
   - Configurar CORS adecuadamente

---

## 🔗 Recursos Adicionales

- [Documentación de Laravel Passport](https://laravel.com/docs/13.x/passport)
- [OAuth2.0 Specification](https://oauth.net/2/)
- [Documentación de Laravel](https://laravel.com/docs/13.x)

---

## 📞 Soporte

Para reportar problemas o sugerencias, por favor abre un issue en el repositorio.
