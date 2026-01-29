#!/bin/bash

echo "=========================================="
echo "  SFS Maquinaria - Iniciando aplicación  "
echo "=========================================="

# Esperar a que la base de datos esté disponible
echo "[1/6] Esperando conexión a la base de datos..."
sleep 10

# Verificar conexión a la base de datos
echo "[2/6] Verificando conexión a MySQL..."
php artisan db:show --counts 2>/dev/null || echo "Advertencia: No se pudo verificar la base de datos"

# Generar key si no existe
echo "[3/6] Verificando APP_KEY..."
php artisan key:generate --force --no-interaction 2>/dev/null || true

# Limpiar caches
echo "[4/6] Limpiando caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Ejecutar migraciones
echo "[5/6] Ejecutando migraciones..."
php artisan migrate --force --no-interaction

# Optimizar para producción
echo "[6/6] Optimizando para producción..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "=========================================="
echo "  Aplicación lista - Iniciando servidor  "
echo "=========================================="

# Iniciar supervisor
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
