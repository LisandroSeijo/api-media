#!/bin/bash

# Script de instalación inicial para el proyecto Laravel con Docker

echo "🚀 Iniciando configuración del proyecto Laravel..."

# Verificar que Docker esté instalado
if ! command -v docker &> /dev/null; then
    echo "❌ Docker no está instalado. Por favor, instala Docker primero."
    exit 1
fi

if ! command -v docker-compose &> /dev/null; then
    echo "❌ Docker Compose no está instalado. Por favor, instala Docker Compose primero."
    exit 1
fi

echo "✅ Docker y Docker Compose encontrados"

# Construir e iniciar los contenedores
echo "📦 Construyendo contenedores Docker..."
docker-compose up -d --build

# Esperar a que los contenedores estén listos
echo "⏳ Esperando a que los contenedores estén listos..."
sleep 10

# Instalar Laravel
echo "📥 Instalando Laravel 12..."
docker-compose exec -T app composer install

# Configurar permisos (solo directorios necesarios)
echo "🔐 Configurando permisos..."
docker-compose exec -T app chmod -R 775 /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true
docker-compose exec -T app chown -R laravel:www-data /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true

# Copiar archivo .env
if [ ! -f .env ]; then
    echo "📝 Creando archivo .env..."
    cp .env.example .env
    
    # Configurar conexión a MySQL (no SQLite)
    echo "🔧 Configurando base de datos MySQL en .env..."
    docker-compose exec -T app sed -i 's/DB_CONNECTION=sqlite/DB_CONNECTION=mysql/' /var/www/.env
    docker-compose exec -T app sed -i 's/# DB_HOST=127.0.0.1/DB_HOST=db/' /var/www/.env
    docker-compose exec -T app sed -i 's/# DB_PORT=3306/DB_PORT=3306/' /var/www/.env
    docker-compose exec -T app sed -i 's/# DB_DATABASE=laravel/DB_DATABASE=laravel/' /var/www/.env
    docker-compose exec -T app sed -i 's/# DB_USERNAME=root/DB_USERNAME=laravel/' /var/www/.env
    docker-compose exec -T app sed -i 's/# DB_PASSWORD=/DB_PASSWORD=root/' /var/www/.env
fi

# Generar clave de aplicación
echo "🔑 Generando clave de aplicación..."
docker-compose exec -T app php artisan key:generate

# Publicar migraciones de Passport (solo si no existen)
echo "📦 Verificando migraciones de Laravel Passport..."
PASSPORT_MIGRATIONS=$(find database/migrations -name "*oauth_auth_codes*" 2>/dev/null | wc -l)
if [ "$PASSPORT_MIGRATIONS" -eq "0" ]; then
    echo "   📝 Publicando migraciones de Passport..."
    docker-compose exec -T app php artisan vendor:publish --tag=passport-migrations --no-interaction
else
    echo "   ✅ Migraciones de Passport ya existen"
fi

# Ejecutar migraciones
echo "🗄️ Ejecutando migraciones..."
sleep 5  # Esperar a que MySQL esté completamente listo
docker-compose exec -T app php artisan migrate --force

# Configurar Passport
echo "🔐 Instalando Laravel Passport..."
if [ -f "storage/oauth-private.key" ]; then
    echo "⚠️  Las claves de Passport ya existen, saltando instalación..."
else
    docker-compose exec -T app php artisan passport:install --no-interaction
fi

# Crear cliente de acceso personal (necesario para generar tokens)
echo "🔑 Creando cliente de acceso personal de Passport..."
docker-compose exec -T app php artisan passport:client --personal --name="Laravel Personal Access Client" --no-interaction 2>/dev/null || echo "   ✅ Cliente personal ya existe o fue creado"

# Publicar configuración de Doctrine
echo "📦 Publicando configuración de Doctrine..."
if [ -f "config/doctrine.php" ]; then
    echo "⚠️  Configuración de Doctrine ya existe, saltando..."
else
    docker-compose exec -T app php artisan vendor:publish --provider="LaravelDoctrine\ORM\DoctrineServiceProvider" --no-interaction
fi

echo ""
echo "✨ ¡Instalación completada!"
echo ""
echo "🌐 Accede a tu aplicación en:"
echo "   - Laravel: http://localhost:8000"
echo "   - PHPMyAdmin: http://localhost:8080"
echo "   - API REST: http://localhost:8000/api/v1"
echo "   - Health Check: http://localhost:8000/api/v1/health"
echo ""
echo "📚 Documentación:"
echo "   - API_DOCUMENTATION.md - Endpoints del API"
echo "   - DOCTRINE_GUIDE.md - Guía de Doctrine ORM"
echo "   - README.md - Guía completa"
echo ""
echo "🧪 Probar el API:"
echo "   ./test-api.sh"
echo ""
echo "📚 Comandos útiles:"
echo "   - Ver logs: docker-compose logs -f app"
echo "   - Artisan: docker-compose exec app php artisan [comando]"
echo "   - Composer: docker-compose exec app composer [comando]"
echo "   - Doctrine: docker-compose exec app php artisan list doctrine"
echo "   - Detener: docker-compose down"
echo ""
