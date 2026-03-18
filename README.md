# 🚀 API REST con OAuth2.0 - Laravel 13

Proyecto de API REST desarrollado con Laravel 13, PHP 8.3, MySQL y autenticación OAuth2.0 usando Laravel Passport.

## 📋 Stack Tecnológico

- **PHP**: 8.3-fpm
- **Laravel Framework**: 13.x
- **MySQL**: 8.0
- **Nginx**: Alpine
- **Laravel Passport**: OAuth2.0 Authentication
- **Docker & Docker Compose**: Containerización

## 🛠️ Instalación

### 1. Clonar el repositorio
```bash
git clone <url-del-repositorio>
cd api
```

### 2. Iniciar Docker
```bash
docker-compose up -d --build
```

### 3. Instalar Laravel (si es necesario)
```bash
docker-compose exec app composer install
```

### 4. Copiar archivo de entorno
```bash
cp .env.example .env
```

### 5. Generar clave de aplicación
```bash
docker-compose exec app php artisan key:generate
```

### 6. Ejecutar migraciones
```bash
docker-compose exec app php artisan migrate
```

### 7. Instalar Passport
```bash
docker-compose exec app php artisan passport:install
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
