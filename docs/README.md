# 📖 Documentación

Bienvenido a la documentación completa del sistema API REST con arquitectura hexagonal.

---

## 🎯 Inicio Rápido

### 🏗️ Ver Arquitectura
👉 **[Diagramas de Componentes por Módulo](./diagrams/INDEX.md)**

### 🔌 Usar la API
👉 **[API Endpoints con ejemplos cURL](../API_ENDPOINTS.md)**

---

## 📚 Contenido

### 1. Diagramas UML

#### Vista General
- **[Diagrama Completo del Sistema (Mermaid)](./UML_Component_Diagram_Mermaid.md)** - Vista general de todos los módulos
- **[Diagrama Completo del Sistema (PlantUML)](./UML_Component_Diagram.puml)** - Para exportar PNG/SVG

#### Por Módulo (Recomendado)
- **[Auth Module](./diagrams/Auth_Module_Diagram.md)** 🔐 - Autenticación con OAuth2 y roles
- **[Media Module](./diagrams/Media_Module_Diagram.md)** 🎬 - Búsqueda de GIFs con GIPHY API
  - [Media Search Sequence](./diagrams/Media_Search_Sequence.md) 🔍 - Flujo detallado de búsqueda
  - [Media Get By ID Sequence](./diagrams/Media_GetById_Sequence.md) 🎯 - Flujo detallado por ID
- **[Audit Module](./diagrams/Audit_Module_Diagram.md)** 📝 - Logging automático de requests
- **[System Module](./diagrams/System_Module_Diagram.md)** ⚙️ - Health checks

👉 **[Ver índice completo de diagramas](./diagrams/INDEX.md)**

---

### 2. Documentación de API

- **[API Endpoints](../API_ENDPOINTS.md)** - Lista completa con ejemplos cURL
- **[API Documentation](../API_DOCUMENTATION.md)** - Guía detallada de uso

---

### 3. Arquitectura

- **[Cursor Rules](../.cursorrules)** - Reglas de arquitectura hexagonal obligatorias
- **[Project Structure](./PROJECT_STRUCTURE.md)** - Estructura de archivos detallada
- **[README Principal](../README.md)** - Información general del proyecto

---

## 🚀 Flujo de Lectura Recomendado

Para nuevos desarrolladores:

1. **Leer**: [README Principal](../README.md) para contexto general
2. **Ver**: [Diagrama Completo](./UML_Component_Diagram_Mermaid.md) para entender la arquitectura
3. **Profundizar**: [Diagramas por Módulo](./diagrams/INDEX.md) para entender cada bounded context
4. **Experimentar**: [API Endpoints](../API_ENDPOINTS.md) para probar la API
5. **Codificar**: [Cursor Rules](../.cursorrules) para seguir las convenciones

---

## 📦 Módulos del Sistema

| Módulo | Descripción | Endpoints | Dependencias |
|--------|-------------|-----------|--------------|
| **Auth** | Autenticación OAuth2 + Roles | `/login`, `/register`, `/logout`, `/user` | Laravel Passport, MySQL |
| **Media** | Búsqueda de contenido multimedia | `/media/search`, `/media/{id}` | GIPHY API, Guzzle |
| **Audit** | Logging automático de peticiones | - (automático) | MySQL |
| **System** | Health checks y estado | `/health` | Config |

---

## 🏗️ Arquitectura Hexagonal

Cada módulo sigue 3 capas:

```
┌─────────────────────────────┐
│     Domain Layer            │  ← Lógica de negocio pura
│  (Entities, Value Objects)  │     Sin dependencias
└─────────────────────────────┘
            ↑
┌─────────────────────────────┐
│   Application Layer         │  ← Casos de uso
│    (Use Cases, DTOs)        │     Orquestación
└─────────────────────────────┘
            ↑
┌─────────────────────────────┐
│  Infrastructure Layer       │  ← Adaptadores
│ (Controllers, Repositories) │     Frameworks
└─────────────────────────────┘
```

**Regla de Oro**: Las capas superiores NO conocen a las inferiores.

---

## 🛠️ Herramientas

### Visualizar Diagramas Mermaid
- **Opción 1**: Abrir archivos `.md` en GitHub (automático)
- **Opción 2**: VS Code con extensión "Markdown Preview Enhanced"
- **Opción 3**: [Mermaid Live Editor](https://mermaid.live/)

### Visualizar/Exportar PlantUML
Ver instrucciones en [`README_DIAGRAMS.md`](./README_DIAGRAMS.md)

---

## 📋 Testing

El sistema cuenta con 42 tests:

- **Unit Tests**: 13 tests - Lógica de negocio aislada
- **Feature Tests**: 15 tests - Endpoints con mocks
- **E2E Tests**: 14 tests (13 passing, 1 skipped) - Flujos completos

```bash
# Ejecutar todos los tests
docker-compose exec app php artisan test

# Solo E2E
docker-compose exec app php artisan test --testsuite=E2E
```

---

## 🔐 Seguridad

- ✅ OAuth2 con Laravel Passport
- ✅ Roles (ADMIN, USER)
- ✅ Middleware de autorización
- ✅ Passwords hasheados
- ✅ Sanitización en audit logs
- ✅ Validación en múltiples capas

---

## 📊 API Overview

### Autenticación
```bash
# Login
curl -X POST "http://localhost:8000/api/v1/login" \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}'

# Usar token
curl -X GET "http://localhost:8000/api/v1/user" \
  -H "Authorization: Bearer {token}"
```

### Media
```bash
# Buscar GIFs
curl -X GET "http://localhost:8000/api/v1/media/search?query=cats&limit=5" \
  -H "Authorization: Bearer {token}"

# Obtener por ID
curl -X GET "http://localhost:8000/api/v1/media/abc123" \
  -H "Authorization: Bearer {token}"
```

### System
```bash
# Health check (público)
curl -X GET "http://localhost:8000/api/v1/health"
```

👉 **[Ver todos los endpoints con ejemplos](../API_ENDPOINTS.md)**

---

## 🔄 Actualizar Documentación

Si modificas el código:

1. **Diagramas Mermaid**: Editar `.md` directamente (auto-render en GitHub)
2. **Diagramas PlantUML**: Editar `.puml` y regenerar imagen
3. **API Endpoints**: Actualizar `API_ENDPOINTS.md`
4. **Tests**: Mantener coverage al 100%

---

## 📞 Links Útiles

| Recurso | Link |
|---------|------|
| **Documentación Completa** | [`/docs/`](.) |
| **Diagramas** | [`/docs/diagrams/`](./diagrams/) |
| **API Endpoints** | [`/API_ENDPOINTS.md`](../API_ENDPOINTS.md) |
| **Cursor Rules** | [`/.cursorrules`](../.cursorrules) |
| **Tests** | [`/tests/`](../tests/) |

---

## 📝 Principios del Proyecto

✅ **Hexagonal Architecture** - Puertos y adaptadores  
✅ **Domain-Driven Design** - Bounded contexts  
✅ **SOLID** - Principios de diseño  
✅ **Tell Don't Ask** - Comportamiento en entidades  
✅ **Dependency Inversion** - Domain independiente  
✅ **Single Action Controllers** - Una acción por controller  
✅ **YAGNI** - Solo código necesario  

---

## 🎯 Comandos Útiles

```bash
# Tests
docker-compose exec app php artisan test
docker-compose exec app php artisan test --filter=AuthenticationFlowTest

# Linters
docker-compose exec app composer lint
docker-compose exec app composer format

# Crear admin
docker-compose exec app php artisan create:admin

# Migraciones
docker-compose exec app php artisan migrate
docker-compose exec app php artisan migrate:fresh --seed

# Cache
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear

# Rutas
docker-compose exec app php artisan route:list
```

---

**Versión**: 1.0.0  
**Fecha**: 2026-03-20  
**Módulos**: 4 (Auth, Media, Audit, System)  
**Tests**: 42 (41 passing, 1 skipped)  
**Coverage**: ~95%
