# 🔄 Reset Completo del Proyecto

## 🎯 Script Creado: `reset-and-install.sh`

Este script hace un **reset completo** del proyecto y lo reinstala desde cero.

## 🧹 Lo que elimina:

1. ✅ **Contenedores Docker** (sin destruirlos, solo los detiene)
2. ✅ **Volumen de MySQL** (elimina toda la base de datos)
3. ✅ **Migraciones de Passport** (todas las duplicadas)
4. ✅ **Claves de Passport** (`storage/oauth-*.key`)
5. ✅ **Archivo .env** (para regenerarlo)
6. ✅ **Cachés de Laravel** (bootstrap/cache, storage/framework)
7. ✅ **Vendor** (para reinstalar dependencias limpias)

## 🚀 Lo que reinstala:

1. ✅ Reconstruye contenedores Docker
2. ✅ Instala dependencias de Composer
3. ✅ Crea nuevo `.env` desde `.env.example`
4. ✅ Genera nueva APP_KEY
5. ✅ Publica migraciones de Passport (correctamente, una sola vez)
6. ✅ Ejecuta todas las migraciones
7. ✅ Instala Passport con claves nuevas
8. ✅ Configura Doctrine

---

## 🎯 Cómo Usarlo

### Opción 1: Script Automatizado (Recomendado)
```bash
./reset-and-install.sh
```

Este comando hace todo el reset e instalación automáticamente.

### Opción 2: Manual
```bash
# 1. Detener y limpiar
docker-compose down
docker volume rm api_mysql_data
rm -f database/migrations/*oauth*.php
rm -f storage/oauth-*.key
rm -f .env
rm -rf vendor

# 2. Reinstalar
./install.sh
```

---

## ⚠️ IMPORTANTE

Este script **ELIMINA TODA LA BASE DE DATOS**. Úsalo solo cuando:
- Estés en desarrollo
- Quieras empezar desde cero
- Tengas problemas de migraciones duplicadas

**NO uses esto en producción sin hacer backup.**

---

## ✅ Después del Reset

Una vez completado, podrás probar:

```bash
# Health check
curl http://localhost:8000/api/v1/health

# Registrar usuario
curl -X POST http://localhost:8000/api/v1/register \
  -H "Content-Type: application/json" \
  -d '{"name":"Test","email":"test@test.com","password":"password123"}'
```

---

## 🎉 Todo Debería Funcionar Perfectamente

Sin duplicados, sin errores, instalación limpia desde cero.
