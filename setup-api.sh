#!/bin/bash

echo "🔐 Configurando API REST con OAuth2.0..."

# Instalar Laravel Passport
echo "📦 Instalando Laravel Passport..."
docker-compose exec -T app composer require laravel/passport

# Ejecutar migraciones de Passport
echo "🗄️ Ejecutando migraciones de Passport..."
docker-compose exec -T app php artisan migrate

# Instalar Passport
echo "⚙️ Instalando Passport..."
docker-compose exec -T app php artisan passport:install --uuids

echo ""
echo "✨ ¡Configuración de OAuth2.0 completada!"
echo ""
echo "📝 Próximos pasos:"
echo "1. Guarda los tokens de cliente que se mostraron arriba"
echo "2. Usa los endpoints de /api para tu aplicación"
echo ""
echo "🔗 Endpoints disponibles:"
echo "   POST /api/register - Registrar nuevo usuario"
echo "   POST /api/login - Iniciar sesión"
echo "   POST /api/logout - Cerrar sesión (requiere token)"
echo "   GET  /api/user - Obtener usuario autenticado (requiere token)"
echo "   GET  /api/posts - Listar posts (requiere token)"
echo ""
