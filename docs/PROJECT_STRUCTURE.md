# 📁 Estructura del Proyecto

Guía visual de la estructura de archivos del proyecto API REST con arquitectura hexagonal.

---

## 🗂️ Vista General

```
api/
├── 📚 docs/                          # Toda la documentación
│   ├── diagrams/                     # Diagramas UML por módulo
│   │   ├── Auth_Module_Diagram.md
│   │   ├── Media_Module_Diagram.md
│   │   ├── Audit_Module_Diagram.md
│   │   ├── System_Module_Diagram.md
│   │   ├── Sequence_Diagrams.md
│   │   └── INDEX.md
│   ├── README.md                     # Índice de documentación
│   ├── INDEX.md                      # Acceso rápido
│   ├── UML_Component_Diagram_Mermaid.md
│   └── UML_Component_Diagram.puml
│
├── 🏗️ src/                            # Código fuente (Arquitectura Hexagonal)
│   ├── Auth/                         # Módulo de Autenticación
│   │   ├── Domain/                   # Lógica de negocio pura
│   │   │   ├── Entities/             # User
│   │   │   ├── ValueObjects/         # Email, Password, Role
│   │   │   ├── Repositories/         # UserRepositoryInterface
│   │   │   └── Services/             # TokenServiceInterface
│   │   ├── Application/              # Casos de uso
│   │   │   ├── UseCases/             # RegisterUser, LoginUser, etc
│   │   │   └── DTOs/                 # RegisterUserDTO, LoginDTO
│   │   └── Infrastructure/           # Adaptadores
│   │       ├── Http/Controllers/     # PostRegisterUserController, etc
│   │       ├── Http/Middleware/      # EnsureUserIsAdmin
│   │       ├── Persistence/Eloquent/ # EloquentUserRepository, UserModel
│   │       └── Services/             # PassportTokenService
│   │
│   ├── Media/                        # Módulo de Media (GIFs)
│   │   ├── Domain/
│   │   │   ├── Entities/             # MediaItem
│   │   │   ├── ValueObjects/         # SearchQuery, Limit, Offset
│   │   │   └── Repositories/         # MediaRepositoryInterface
│   │   ├── Application/
│   │   │   ├── UseCases/             # SearchMedia, GetMediaById
│   │   │   └── DTOs/                 # SearchMediaDTO, GetMediaByIdDTO
│   │   └── Infrastructure/
│   │       ├── Http/Controllers/     # GetMediaSearchController, etc
│   │       └── Persistence/Http/     # GiphyMediaRepository
│   │
│   ├── Audit/                        # Módulo de Auditoría
│   │   ├── Domain/
│   │   │   ├── Entities/             # AuditLog
│   │   │   └── Repositories/         # AuditLogRepositoryInterface
│   │   ├── Application/
│   │   │   ├── UseCases/             # CreateAuditLog
│   │   │   └── DTOs/                 # CreateAuditLogDTO
│   │   └── Infrastructure/
│   │       ├── Listeners/            # LogRequestAudited (Event Listener)
│   │       └── Persistence/Eloquent/ # EloquentAuditLogRepository, AuditLogModel
│   │
│   └── System/                       # Módulo de Sistema
│       ├── Domain/
│       │   ├── Entities/             # SystemHealth
│       │   └── ValueObjects/         # Version
│       ├── Application/
│       │   └── UseCases/             # GetSystemHealth
│       └── Infrastructure/
│           └── Http/Controllers/     # GetSystemHealthController
│
├── 🧪 tests/                          # Tests (PHPUnit)
│   ├── Unit/                         # Tests unitarios
│   │   └── Auth/                     # Tests de Value Objects, etc
│   ├── Feature/                      # Tests de endpoints
│   │   ├── Auth/                     # RegisterUserTest, LoginUserTest, etc
│   │   └── Media/                    # SearchMediaTest
│   └── E2E/                          # Tests end-to-end
│       ├── E2ETestCase.php           # Base class con helpers
│       ├── Auth/                     # AuthenticationFlowTest
│       ├── Media/                    # MediaSearchFlowTest, MediaErrorHandlingTest
│       └── Audit/                    # AuditLoggingTest
│
├── 🗄️ database/                       # Base de datos
│   ├── migrations/                   # Migraciones de BD
│   ├── factories/                    # Factories para tests
│   └── seeders/                      # Seeders (AdminUserSeeder)
│
├── 🛤️ routes/                         # Rutas
│   └── api.php                       # Todas las rutas de la API
│
├── ⚙️ config/                         # Configuración
│   ├── app.php                       # Config de app (version, env)
│   ├── auth.php                      # Config de autenticación
│   ├── services.php                  # Config de servicios (GIPHY API)
│   └── ...
│
├── 🏭 app/                            # Laravel tradicional
│   ├── Console/Commands/             # CreateAdminCommand
│   ├── Http/Controllers/             # Base Controller
│   ├── Http/Middleware/              # ForceJsonResponse, etc
│   ├── Providers/                    # AppServiceProvider, EventServiceProvider
│   └── ...
│
├── 📄 Archivos Raíz
│   ├── .cursorrules                  # Reglas de arquitectura hexagonal
│   ├── README.md                     # README principal
│   ├── API_ENDPOINTS.md              # Lista completa de endpoints
│   ├── API_DOCUMENTATION.md          # Documentación de la API
│   ├── composer.json                 # Dependencias PHP
│   ├── docker-compose.yml            # Configuración Docker
│   ├── phpunit.xml                   # Configuración de tests
│   └── .env.example                  # Variables de entorno
│
└── 🐳 Docker
    ├── nginx/                        # Configuración Nginx
    └── php/                          # Dockerfile PHP 8.3
```

---

## 🏗️ Arquitectura Hexagonal - Explicación Visual

Cada módulo (`Auth`, `Media`, `Audit`, `System`) sigue esta estructura:

```
📦 Module/
│
├── 🎯 Domain/                        # CAPA 1: Núcleo del Negocio
│   │                                 # ✅ Sin dependencias externas
│   │                                 # ✅ PHP puro
│   │                                 # ✅ Lógica de negocio
│   ├── Entities/                     # Objetos con identidad
│   │   └── EntityName.php
│   ├── ValueObjects/                 # Objetos inmutables
│   │   └── ValueObjectName.php
│   ├── Repositories/                 # Contratos (Interfaces)
│   │   └── RepositoryInterface.php
│   └── Services/                     # Servicios de dominio
│       └── ServiceInterface.php
│
├── 🔄 Application/                   # CAPA 2: Casos de Uso
│   │                                 # ✅ Depende solo de Domain
│   │                                 # ✅ Orquestación
│   ├── UseCases/                     # Lógica de aplicación
│   │   └── UseCaseName.php
│   └── DTOs/                         # Data Transfer Objects
│       └── DTOName.php
│
└── 🔌 Infrastructure/                # CAPA 3: Adaptadores
    │                                 # ✅ Depende de Domain + Application
    │                                 # ✅ Frameworks (Laravel, Eloquent, etc)
    ├── Http/Controllers/             # Controllers HTTP (Single Action)
    │   └── ControllerName.php
    ├── Http/Middleware/              # Middleware de Laravel
    │   └── MiddlewareName.php
    ├── Persistence/                  # Implementaciones de repositorios
    │   ├── Eloquent/                 # Implementación con Eloquent
    │   │   ├── Repositories/         # EloquentRepository
    │   │   └── Models/               # Eloquent Models
    │   └── Http/                     # Implementación con HTTP
    │       └── HttpRepository.php    # Repositorio para APIs externas
    ├── Listeners/                    # Event Listeners
    │   └── ListenerName.php
    └── Services/                     # Implementaciones de servicios
        └── ServiceImpl.php
```

---

## 🎯 Flujo de Dependencias

```
Infrastructure → Application → Domain
     (3)             (2)          (1)

✅ Infrastructure puede depender de Application y Domain
✅ Application puede depender de Domain
❌ Domain NO depende de nadie

Ejemplo:
Controller (Infrastructure) 
    → invoca UseCase (Application)
        → usa Repository Interface (Domain)
            ← implementado por EloquentRepository (Infrastructure)
```

---

## 📁 Convenciones de Naming

### Domain Layer

```php
// Entities (sustantivos singulares)
User.php, MediaItem.php, AuditLog.php

// Value Objects (sustantivos descriptivos)
Email.php, Password.php, SearchQuery.php, Limit.php

// Repository Interfaces (Entity + RepositoryInterface)
UserRepositoryInterface.php
MediaRepositoryInterface.php
```

### Application Layer

```php
// Use Cases (Verbo + Sustantivo)
RegisterUser.php, LoginUser.php, SearchMedia.php

// DTOs (Acción + DTO)
RegisterUserDTO.php, SearchMediaDTO.php
```

### Infrastructure Layer

```php
// Controllers (HTTP Method + Action + Controller)
PostRegisterUserController.php  // POST /register
GetMediaSearchController.php    // GET /media/search

// Repositories (ORM + Entity + Repository)
EloquentUserRepository.php
GiphyMediaRepository.php

// Models (Entity + Model)
UserModel.php, AuditLogModel.php

// Services (Framework + Service)
PassportTokenService.php
```

---

## 🧪 Tests - Estructura

```
tests/
│
├── Unit/                             # Tests de lógica aislada
│   ├── Auth/
│   │   └── ValueObjects/
│   │       └── ValueObjectsTest.php  # Email, Password, Role
│   └── ...
│
├── Feature/                          # Tests de endpoints (con mocks)
│   ├── PassportTestCase.php          # Base class para tests con Passport
│   ├── Auth/
│   │   ├── RegisterUserTest.php      # POST /register
│   │   └── LoginUserTest.php         # POST /login
│   └── Media/
│       └── SearchMediaTest.php       # GET /media/search
│
└── E2E/                              # Tests end-to-end (flujos completos)
    ├── E2ETestCase.php               # Base class con helpers
    ├── Auth/
    │   └── AuthenticationFlowTest.php # Flujo completo de auth
    ├── Media/
    │   ├── MediaSearchFlowTest.php    # Flujo de búsqueda
    │   └── MediaErrorHandlingTest.php # Manejo de errores
    └── Audit/
        └── AuditLoggingTest.php       # Verificación de audit logs
```

---

## 🗄️ Database - Tablas

```sql
-- Laravel/Passport
users                 -- Usuarios (con role enum)
oauth_access_tokens   -- Tokens OAuth2
oauth_clients         -- Clientes OAuth2
...

-- Audit
audit_logs            -- Logs de auditoría
  ├── user_id (índice)
  ├── service (índice)
  ├── response_code (índice)
  └── created_at (índice)
```

---

## 🔐 Configuración - Variables de Entorno

```env
# .env
APP_VERSION=1.0.0
APP_ENV=production

DB_CONNECTION=mysql
DB_HOST=db
DB_DATABASE=api_db

GIPHY_API_KEY=your_api_key_here
```

---

## 📚 Documentación - Navegación

```
docs/
├── README.md                         # 👈 EMPEZAR AQUÍ
├── INDEX.md                          # Acceso rápido
├── diagrams/
│   ├── INDEX.md                      # 👈 DIAGRAMAS AQUÍ
│   ├── Auth_Module_Diagram.md        # Módulo Auth
│   ├── Media_Module_Diagram.md       # Módulo Media
│   ├── Audit_Module_Diagram.md       # Módulo Audit
│   ├── System_Module_Diagram.md      # Módulo System
│   └── Sequence_Diagrams.md          # Diagramas de flujo
└── ...

Raíz/
├── API_ENDPOINTS.md                  # 👈 ENDPOINTS AQUÍ
└── API_DOCUMENTATION.md              # Guía de la API
```

---

## 🚀 Comandos Útiles por Ubicación

### Raíz del Proyecto
```bash
# Docker
docker-compose up -d
docker-compose down

# Tests
docker-compose exec app php artisan test
docker-compose exec app php artisan test --testsuite=E2E

# Instalación
./install.sh
```

### Dentro del Contenedor
```bash
# Entrar al contenedor
docker-compose exec app bash

# Migraciones
php artisan migrate
php artisan migrate:fresh --seed

# Crear admin
php artisan create:admin

# Cache
php artisan cache:clear
php artisan config:clear

# Rutas
php artisan route:list
```

---

## 📊 Métricas del Proyecto

| Métrica | Valor |
|---------|-------|
| Módulos | 4 (Auth, Media, Audit, System) |
| Endpoints | 8 |
| Tests Totales | 42 (41 passing, 1 skipped) |
| Coverage | ~95% |
| Archivos PHP | ~80 |
| Líneas de Código | ~5,000 |
| Documentación | 10+ archivos MD |
| Diagramas | 6 (4 módulos + 1 completo + 1 secuencia) |

---

## 🎯 Principios Aplicados

✅ **Hexagonal Architecture** - Puertos y adaptadores  
✅ **Domain-Driven Design** - Bounded contexts por módulo  
✅ **SOLID Principles** - Cada clase una responsabilidad  
✅ **Clean Code** - Nombres descriptivos, funciones pequeñas  
✅ **Test-Driven** - Coverage del 95%  
✅ **Documentation First** - Documentación completa  

---

**Última actualización**: 2026-03-20  
**Versión**: 1.0.0
