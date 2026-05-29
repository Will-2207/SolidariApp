FROM php:8.2-apache

# 1. Dependencias del sistema necesarias
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    pkg-config \
    libssl-dev \
    ca-certificates \
    && update-ca-certificates \
    && rm -rf /var/lib/apt/lists/*

# 2. Habilitar mod_rewrite para Apache (Necesario para rutas amigables)
RUN a2enmod rewrite

# 3. Instalamos la extensión de MongoDB
RUN pecl install mongodb \
    && docker-php-ext-enable mongodb

# 4. Configuración de Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

WORKDIR /var/www/html

# 5. Copiamos el código fuente
COPY . .

# 6. Instalación de dependencias
RUN composer install --no-dev --optimize-autoloader --ignore-platform-req=ext-mongodb

# 7. Permisos (Importante para que Apache pueda leer los archivos)
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# 8. Exponer puerto y comando final
EXPOSE 80
