# syntax=docker/dockerfile:1

# ============================================
# ETAPA 1: Construcción de assets frontend
# ============================================
FROM node:22-bookworm-slim AS assets
WORKDIR /app

# Instalar dependencias (caché separado del código fuente)
COPY package.json package-lock.json ./
RUN npm ci

# Copiar el resto del código y construir
COPY . .
RUN npm run build


# ============================================
# ETAPA 2: Instalación de dependencias PHP (vendor)
# ============================================
FROM composer:2 AS vendor
WORKDIR /app

# Copiar solo los archivos de definición de dependencias
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts


# ============================================
# ETAPA 3: Imagen final de la aplicación
# ============================================
FROM dunglas/frankenphp:1-php8.4-bookworm AS application
WORKDIR /app

# Configurar PHP y extensiones (no depende del código, se cachea)
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && install-php-extensions \
        pdo_pgsql \
        mbstring \
        intl \
        zip \
        opcache \
        bcmath

# Render utiliza un puerto no privilegiado, por lo que esta
# capacidad del ejecutable FrankenPHP no es necesaria.
RUN setcap -r /usr/local/bin/frankenphp
        
# Copiar el código fuente de la aplicación
COPY . .

# Copiar los artefactos construidos desde las etapas anteriores
COPY --from=vendor /app/vendor ./vendor
COPY --from=assets /app/public/build ./public/build

# Copiar configuración de Caddy y script de arranque
COPY Caddyfile /etc/caddy/Caddyfile
COPY scripts/render-start.sh /usr/local/bin/render-start

# Crear directorios necesarios, asignar propietario y permisos
RUN chmod +x /usr/local/bin/render-start \
    && mkdir -p \
        storage/framework/cache \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwX storage bootstrap/cache

# Variables de entorno para producción
ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr

# Puerto expuesto
EXPOSE 10000

# Comando de inicio
CMD ["/usr/local/bin/render-start"]