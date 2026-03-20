# 🚀 API REST con OAuth2.0 - Laravel 12

Proyecto de API REST desarrollado con Laravel 12, PHP 8.3, MySQL, autenticación OAuth2.0 usando Laravel Passport y Doctrine ORM.

**Arquitectura**: Hexagonal (Ports & Adapters) con Domain-Driven Design (DDD)

---

## 📚 Documentación

### 🏗️ Diagramas de Arquitectura
👉 **[Ver Diagramas UML por Módulo](./docs/diagrams/INDEX.md)**

- **[Auth Module](./docs/diagrams/Auth_Module_Diagram.md)** 🔐 - Autenticación OAuth2 y roles
- **[Media Module](./docs/diagrams/Media_Module_Diagram.md)** 🎬 - Búsqueda de contenido multimedia
- **[Audit Module](./docs/diagrams/Audit_Module_Diagram.md)** 📝 - Logging automático de requests
- **[System Module](./docs/diagrams/System_Module_Diagram.md)** ⚙️ - Health checks

### 📖 API
- **[API Endpoints](./API_ENDPOINTS.md)** - Todos los endpoints con ejemplos cURL
- **[API Documentation](./API_DOCUMENTATION.md)** - Guía completa de uso

### 📦 Documentación Completa
👉 **[Acceso a Toda la Documentación](./docs/README.md)**

---

## 📋 Stack Tecnológico

- **PHP**: 8.3-fpm
- **Laravel Framework**: 12.x
- **MySQL**: 8.0
- **Nginx**: Alpine
- **Laravel Passport**: OAuth2.0 Authentication
- **Laravel Doctrine ORM**: 3.3.2
- **Doctrine ORM**: 3.6.2
- **Docker & Docker Compose**: Containerización

## 🛠️ Instalación

### Opción 1: Instalación Automática (Recomendada)

```bash
# 1. Clonar el repositorio
git clone <url-del-repositorio>
cd api

# 2. Iniciar Docker
docker-compose up -d --build

# 3. Ejecutar script de instalación
./install.sh
```

### Opción 2: Instalación Manual

```bash
# 1. Clonar el repositorio
git clone <url-del-repositorio>
cd api

# 2. Iniciar Docker
docker-compose up -d --build

# 3. Instalar dependencias
docker-compose exec app composer install

# 4. Configurar entorno
cp .env.example .env
docker-compose exec app php artisan key:generate

# 5. Ejecutar migraciones
docker-compose exec app php artisan migrate

# 6. Instalar Passport
docker-compose exec app php artisan passport:install

# 7. Publicar configuración de Doctrine
docker-compose exec app php artisan vendor:publish --provider="LaravelDoctrine\ORM\DoctrineServiceProvider"
```

### Opción 3: Usando Composer Scripts

```bash
# Después de iniciar Docker
docker-compose exec app composer setup
```

## 🌐 URLs de Acceso

- **API REST**: http://localhost:8000/api/v1
- **PHPMyAdmin**: http://localhost:8080
  - Servidor: `db`
  - Usuario: `laravel`
  - Contraseña: `root`

## 📚 Documentación

Consulta la documentación completa del API en:
- [API_DOCUMENTATION.md](./API_DOCUMENTATION.md) - Documentación completa de endpoints
- [POSTMAN_COLLECTION.md](./POSTMAN_COLLECTION.md) - Colección para Postman

## 🔐 Endpoints Principales

### Públicos (sin autenticación)
- `GET /api/v1/health` - Health check
- `POST /api/v1/register` - Registrar usuario
- `POST /api/v1/login` - Iniciar sesión

### Protegidos (requieren token)
- `GET /api/v1/user` - Obtener usuario autenticado
- `POST /api/v1/logout` - Cerrar sesión
- `GET /api/v1/posts` - Listar posts
- `POST /api/v1/posts` - Crear post
- `GET /api/v1/posts/{id}` - Obtener post
- `PUT /api/v1/posts/{id}` - Actualizar post
- `DELETE /api/v1/posts/{id}` - Eliminar post

## 🧪 Probar la API

### Opción 1: Script de prueba automatizado
```bash
./test-api.sh
```

### Opción 2: cURL manual
```bash
# 1. Registrar usuario
curl -X POST http://localhost:8000/api/v1/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'

# 2. Guardar el token de la respuesta
TOKEN="tu_token_aqui"

# 3. Obtener información del usuario
curl -X GET http://localhost:8000/api/v1/user \
  -H "Authorization: Bearer $TOKEN"
```

### Opción 3: Postman
Importa la colección desde `POSTMAN_COLLECTION.md`

## 📝 Comandos Útiles

### Docker
```bash
# Ver logs
docker-compose logs -f app

# Acceder al contenedor
docker-compose exec app bash

# Detener contenedores
docker-compose down

# Reiniciar contenedores
docker-compose restart
```

### Laravel
```bash
# Artisan
docker-compose exec app php artisan [comando]

# Composer
docker-compose exec app composer [comando]

# Migraciones
docker-compose exec app php artisan migrate

# Ver rutas
docker-compose exec app php artisan route:list
```

### Passport
```bash
# Crear nuevo cliente
docker-compose exec app php artisan passport:client

# Ver clientes
docker-compose exec app php artisan passport:client --list

# Limpiar tokens expirados
docker-compose exec app php artisan passport:purge
```

## 🗂️ Estructura del Proyecto

```
.
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── API/
│   │           ├── AuthController.php
│   │           └── PostController.php
│   └── Models/
│       └── User.php
├── config/
│   ├── auth.php
│   └── passport.php
├── database/
│   └── migrations/
├── docker/
│   ├── mysql/
│   ├── nginx/
│   └── php/
├── routes/
│   ├── api.php
│   └── web.php
├── docker-compose.yml
├── Dockerfile
├── API_DOCUMENTATION.md
├── POSTMAN_COLLECTION.md
├── test-api.sh
└── README.md
```

## 🔧 Configuración

### Base de Datos
Las credenciales se configuran en el archivo `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=root
```

### OAuth2.0
Laravel Passport está configurado en:
- `config/auth.php` - Guard API configurado con Passport
- `app/Models/User.php` - Trait HasApiTokens incluido
- `routes/api.php` - Rutas protegidas con middleware `auth:api`

### CORS
Si necesitas acceder desde un frontend en otro dominio, configura CORS en:
```bash
docker-compose exec app php artisan config:publish cors
```

## 🔍 Características

✅ Autenticación OAuth2.0 con Laravel Passport  
✅ Registro e inicio de sesión de usuarios  
✅ Endpoints protegidos con tokens Bearer  
✅ CRUD completo de posts (ejemplo)  
✅ Respuestas JSON estandarizadas  
✅ Validación de datos  
✅ Manejo de errores  
✅ Documentación completa  
✅ Colección de Postman incluida  
✅ Script de pruebas automatizado  
✅ Dockerizado y listo para producción  

## 🚀 Próximos Pasos

- [ ] Implementar refresh tokens
- [ ] Agregar roles y permisos
- [ ] Implementar rate limiting personalizado
- [ ] Agregar más endpoints (categorías, comentarios, etc.)
- [ ] Implementar paginación
- [ ] Agregar tests unitarios y de integración
- [ ] Configurar CI/CD
- [ ] Documentación con Swagger/OpenAPI

## 🐛 Solución de Problemas

### Token inválido o expirado
```bash
# Limpiar y regenerar tokens
docker-compose exec app php artisan passport:purge
docker-compose exec app php artisan passport:install --force
```

### Error de conexión a la base de datos
```bash
# Verificar que MySQL esté corriendo
docker-compose ps

# Reiniciar contenedores
docker-compose restart db app
```

### Permisos
```bash
# Ajustar permisos
docker-compose exec app chown -R laravel:www-data /var/www
docker-compose exec app chmod -R 775 /var/www/storage /var/www/bootstrap/cache
```

## 📄 Licencia

Este proyecto es de código abierto bajo la [Licencia MIT](LICENSE).

## 🤝 Contribuir

Las contribuciones son bienvenidas! Por favor:

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## 📞 Soporte

Para reportar problemas o sugerencias, por favor abre un issue en el repositorio.

---

**Desarrollado con ❤️ usando Laravel 13**
