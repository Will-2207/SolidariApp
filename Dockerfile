
# ── Base: PHP 8.2 con Apache ──────────────────────────────────────────────
FROM php:8.2-apache
 
# Variables de entorno (sobreescribir en producción o docker-compose.yml)
ENV MONGO_URI="mongodb+srv://<usuario>:<password>@<cluster>.mongodb.net/?retryWrites=true&w=majority"
ENV MONGO_DB="solidariapp"
 
# ── Dependencias del sistema ───────────────────────────────────────────────
RUN apt-get update && apt-get install -y \
    libssl-dev \
    git \
    unzip \
    curl \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*
 
# ── Extensión MongoDB para PHP (vía PECL) ─────────────────────────────────
RUN pecl install mongodb \
    && docker-php-ext-enable mongodb
 
# ── Composer ──────────────────────────────────────────────────────────────
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
 
# ── Habilitar mod_rewrite de Apache ───────────────────────────────────────
RUN a2enmod rewrite
 
# ── Copiar proyecto ───────────────────────────────────────────────────────
WORKDIR /var/www/html
COPY . .
 
# ── Instalar dependencias PHP (sin dev, autoloader optimizado) ────────────
RUN composer install --no-dev --optimize-autoloader --no-interaction
 
# ── Permisos ──────────────────────────────────────────────────────────────
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html
 
# ── Exponer puerto ────────────────────────────────────────────────────────
EXPOSE 80
