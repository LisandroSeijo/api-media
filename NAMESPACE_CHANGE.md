# ✅ Cambio de Namespace: Src\ → Api\

Se ha cambiado el namespace de todos los módulos de negocio de `Src\` a `Api\`.

## 📝 Archivos Actualizados:

### 1. Configuración:
- ✅ `composer.json` - PSR-4 autoload cambiado a `"Api\\": "src/"`
- ✅ `app/Providers/AppServiceProvider.php` - Bindings actualizados
- ✅ `routes/api.php` - Imports actualizados

### 2. Módulo Auth (12 archivos):
- ✅ Domain/Entities/User.php
- ✅ Domain/ValueObjects/Email.php
- ✅ Domain/ValueObjects/Password.php
- ✅ Domain/Repositories/UserRepositoryInterface.php
- ✅ Application/DTOs/RegisterUserDTO.php
- ✅ Application/DTOs/LoginDTO.php
- ✅ Application/UseCases/RegisterUser.php
- ✅ Application/UseCases/LoginUser.php
- ✅ Application/UseCases/LogoutUser.php
- ✅ Infrastructure/Persistence/Eloquent/Models/UserModel.php
- ✅ Infrastructure/Persistence/Eloquent/Repositories/EloquentUserRepository.php
- ✅ Infrastructure/Http/Controllers/AuthController.php

## 🚀 Ahora ejecuta:

```bash
# Regenerar autoload de Composer
docker-compose exec app composer dump-autoload

# Limpiar cachés
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan route:clear

# Probar
curl http://localhost:8000/api/v1/health
```

## 📁 Namespace Final:

```php
// Antes
use Src\Auth\Domain\Entities\User;

// Ahora
use Api\Auth\Domain\Entities\User;
```

¡Todo listo! 🎉
