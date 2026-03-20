# Diagramas UML - Arquitectura del Sistema

Este directorio contiene los diagramas UML que documentan la arquitectura del sistema API REST.

## 📄 Archivos Disponibles

- **`UML_Component_Diagram.puml`** - Diagrama de componentes en formato PlantUML (requiere herramienta)
- **`UML_Component_Diagram_Mermaid.md`** - Diagrama de componentes en formato Mermaid (se visualiza en GitHub)

## 🚀 Visualización Rápida

### ⭐ Opción Recomendada: Mermaid (GitHub)

Simplemente abre el archivo [`UML_Component_Diagram_Mermaid.md`](./UML_Component_Diagram_Mermaid.md) en GitHub o VS Code con el plugin Markdown Preview.

**Ventajas**:
- ✅ Se visualiza directamente en GitHub sin herramientas
- ✅ Compatible con VS Code, Obsidian, Notion, etc.
- ✅ Fácil de editar y mantener

### Opción 2: PlantUML (Mayor Control)

Para mayor personalización y control, usa el archivo `UML_Component_Diagram.puml`.

## 🎯 Propósito

El diagrama muestra:
- ✅ Los 4 módulos verticales (Auth, Media, Audit, System)
- ✅ Las 3 capas de arquitectura hexagonal por módulo (Domain, Application, Infrastructure)
- ✅ Las dependencias entre capas respetando inversión de dependencias
- ✅ Los puertos (interfaces) y adaptadores (implementaciones)
- ✅ Las integraciones con servicios externos (GIPHY, MySQL)
- ✅ El framework Laravel y sus componentes utilizados

## 🖼️ Cómo Visualizar el Diagrama

### Opción 1: VS Code (Recomendado)

1. Instala la extensión **PlantUML** de jebbs:
   ```
   code --install-extension jebbs.plantuml
   ```

2. Abre el archivo `UML_Component_Diagram.puml`

3. Presiona `Alt + D` (o `Cmd + D` en Mac) para ver el preview

4. Para exportar como imagen:
   - Click derecho en el archivo → "Export Current Diagram"
   - O presiona `Ctrl + Shift + P` → "PlantUML: Export Current Diagram"

### Opción 2: IntelliJ IDEA / PHPStorm

1. Instala el plugin **PlantUML integration**

2. Abre el archivo `UML_Component_Diagram.puml`

3. El preview se mostrará automáticamente en el panel lateral

### Opción 3: Online (Sin instalación)

1. Ve a [PlantUML Online Editor](http://www.plantuml.com/plantuml/uml/)

2. Copia y pega el contenido de `UML_Component_Diagram.puml`

3. El diagrama se renderizará automáticamente

4. Puedes descargar como PNG, SVG, o PDF

### Opción 4: Docker (Generar PNG directamente)

```bash
# Desde la raíz del proyecto
docker run --rm \
  -v $(pwd)/docs:/data \
  plantuml/plantuml:latest \
  -tpng /data/UML_Component_Diagram.puml

# Se generará: docs/UML_Component_Diagram.png
```

### Opción 5: CLI (Si tienes Java instalado)

```bash
# Instalar PlantUML
brew install plantuml  # macOS
# o
sudo apt install plantuml  # Linux

# Generar PNG
plantuml docs/UML_Component_Diagram.puml

# Generar SVG (mejor calidad)
plantuml -tsvg docs/UML_Component_Diagram.puml
```

## 📊 Estructura del Diagrama

### Módulos Principales

1. **Auth Module** (Azul)
   - Gestión de usuarios, autenticación y autorización
   - Integración con Laravel Passport (OAuth2)
   - Middleware de administrador

2. **Media Module** (Verde)
   - Búsqueda y obtención de media
   - Integración con GIPHY API vía Guzzle
   - Endpoints protegidos por autenticación

3. **Audit Module** (Amarillo)
   - Registro automático de requests/responses
   - Event-driven con Laravel Events
   - Sanitización de datos sensibles

4. **System Module** (Rojo)
   - Health checks
   - Información del sistema
   - Endpoint público

### Capas de Arquitectura Hexagonal

- **Domain** (Blanco): Lógica de negocio pura, sin dependencias externas
- **Application** (Gris claro): Casos de uso y orquestación
- **Infrastructure** (Gris oscuro): Detalles técnicos, frameworks, BD

### Principios Aplicados

- ✅ **Dependency Inversion**: Domain no depende de nadie
- ✅ **Ports & Adapters**: Interfaces en Domain, implementaciones en Infrastructure
- ✅ **Separation of Concerns**: Cada capa tiene responsabilidades claras
- ✅ **Bounded Contexts**: Módulos verticales independientes

## 🔄 Actualizar el Diagrama

Si haces cambios en la arquitectura:

1. Edita el archivo `UML_Component_Diagram.puml`
2. Regenera la imagen (cualquiera de las opciones anteriores)
3. Commit ambos archivos (.puml y .png)

## 📚 Documentación Adicional

- [PlantUML Component Diagram](https://plantuml.com/component-diagram)
- [Arquitectura Hexagonal](https://alistair.cockburn.us/hexagonal-architecture/)
- Reglas del proyecto: `.cursorrules`

## 📝 Leyenda

- **Línea punteada con triángulo blanco** (`..|>`) = Implementa interfaz
- **Línea punteada con flecha** (`..>`) = Usa/Depende de
- **Línea sólida con flecha** (`-->`) = Invoca/Llama a
- **Colores**: Indican agrupación por módulo o capa

---

**Fecha de creación**: 2026-03-20  
**Autor**: Sistema de desarrollo  
**Versión**: 1.0.0
