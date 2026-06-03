FROM php:8.2-apache

# 1. Instalar dependencias necesarias para compilar extensiones PHP
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libssl-dev \
    libcurl4-openssl-dev \
    pkg-config \
    && rm -rf /var/lib/apt/lists/*

# 2. Instalar el driver de MongoDB (extensión PHP)
# Se requiere 'pecl' y las librerías de arriba para que compile correctamente
RUN pecl install mongodb \
    && docker-php-ext-enable mongodb

# 3. Habilitar mod_rewrite
RUN a2enmod rewrite

# 4. Configurar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

WORKDIR /var/www/html

# 5. Copiar solo archivos de dependencias para caché
COPY composer.json composer.lock* ./

# 6. Instalar dependencias con verbosidad para ver errores si fallan
# El --ignore-platform-reqs ayuda si hay discrepancias menores de versión
RUN composer install --no-dev --optimize-autoloader --no-interaction

# 7. Copiar el resto del código
COPY . .

# 8. Permisos
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
