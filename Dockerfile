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

# 2. Instalamos la extensión (si esto falla por RAM, es el límite de Render)
RUN pecl install mongodb \
    && docker-php-ext-enable mongodb

# 3. Configuración de Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

WORKDIR /var/www/html

# 4. Copiamos el código
COPY . .

# 5. Instalación de dependencias (Usando el flag que te funcionó)
# Esto evita que Composer falle si detecta algo extraño en la extensión
RUN composer install --no-dev --optimize-autoloader --ignore-platform-req=ext-mongodb

# 6. Permisos (Importante para que Apache pueda leer tus archivos)
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 80
