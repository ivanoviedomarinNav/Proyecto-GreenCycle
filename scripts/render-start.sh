#!/bin/sh

# ============================================
# 1. CONFIGURACIÓN INICIAL Y SEGURIDAD
# ============================================
set -eu  # Salir si falla un comando (e) o si se usa variable indefinida (u)

cd /app

# ============================================
# 2. VARIABLES DE ENTORNO
# ============================================
echo "Preparing Laravel production environment..."

# Si Render define la URL externa, la usamos como APP_URL y ASSET_URL
if [ -n "${RENDER_EXTERNAL_URL:-}" ]; then
    export APP_URL="${APP_URL:-$RENDER_EXTERNAL_URL}"
    export ASSET_URL="${ASSET_URL:-$RENDER_EXTERNAL_URL}"
fi

# ============================================
# 3. PERMISOS Y ESTRUCTURA DE DIRECTORIOS
# ============================================
# Crear los directorios necesarios para Laravel
mkdir -p \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

# Asignar propietario y permisos recursivos
chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache

# ============================================
# 4. COMANDOS DE ARTISAN (LARAVEL)
# ============================================
echo "Discovering Laravel packages..."
php artisan package:discover --ansi

echo "Running database migrations..."
php artisan migrate --force

echo "Optimizing Laravel..."
php artisan optimize

# ============================================
# 5. INICIO DEL SERVIDOR FRANKENPHP
# ============================================
echo "Starting FrankenPHP on port ${PORT:-10000}..."

# exec reemplaza el shell actual por FrankenPHP (mejor para señales y PID 1)
exec frankenphp run \
    --config /etc/caddy/Caddyfile \
    --adapter caddyfile

# Qué realiza este script:
# 1. Se ubica en /app.
# 2. Obtiene la URL pública creada automáticamente por Render.
# 3. Configura APP_URL y ASSET_URL.
# 4. Prepara las carpetas escribibles.
# 5. Ejecuta el descubrimiento de paquetes.
# 6. Ejecuta las migraciones en Neon production.
# 7. Optimiza configuración, rutas, eventos y vistas.
# 8.Inicia FrankenPHP.