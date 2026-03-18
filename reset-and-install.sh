#!/bin/bash

echo "🧹 ======================================"
echo "🧹 RESET COMPLETO DEL PROYECTO"
echo "🧹 ======================================"
echo ""

# Detener contenedores
echo "🛑 Deteniendo contenedores..."
docker-compose down

# Eliminar volúmenes de la base de datos
echo "🗑️  Eliminando volúmenes de la base de datos..."
docker volume rm api_mysql_data 2>/dev/null || true

# Eliminar todas las migraciones de Passport existentes
echo "🗑️  Eliminando migraciones duplicadas de Passport..."
rm -f database/migrations/*oauth*.php

# Eliminar claves de Passport
echo "🗑️  Eliminando claves de Passport..."
rm -f storage/oauth-*.key
rm -f storage/*.key

# Eliminar archivo .env
echo "🗑️  Eliminando .env antiguo..."
rm -f .env

# Eliminar base de datos SQLite si existe
echo "🗑️  Eliminando base de datos SQLite..."
rm -f database/database.sqlite
rm -f database/*.sqlite

# Limpiar cachés de Laravel
echo "🗑️  Limpiando archivos de caché..."
rm -rf bootstrap/cache/*.php
rm -rf storage/framework/cache/*
rm -rf storage/framework/sessions/*
rm -rf storage/framework/views/*

# Eliminar vendor (opcional, para reinstalar todo)
echo "🗑️  Eliminando vendor (para reinstalación limpia)..."
rm -rf vendor

echo ""
echo "✅ Limpieza completa finalizada"
echo ""
echo "🚀 Ahora ejecutando instalación desde cero..."
echo ""

# Ejecutar instalación desde cero
./install.sh

echo ""
echo "🎉 ======================================"
echo "🎉 RESET E INSTALACIÓN COMPLETADOS"
echo "🎉 ======================================"
echo ""
echo "🧪 Probar la API:"
echo ""
echo "curl -X POST http://localhost:8000/api/v1/register \\"
echo "  -H \"Content-Type: application/json\" \\"
echo "  -d '{\"name\":\"Test User\",\"email\":\"test@test.com\",\"password\":\"password123\"}'"
