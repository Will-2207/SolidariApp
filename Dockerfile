FROM php:8.2-apache

# Instalar dependencias del sistema y el driver de mongodb
# 'libssl-dev' es necesario para la conexión, pero aquí lo instalamos de forma que no sature la memoria
RUN apt-get update && apt-get install -y \
    libssl-dev \
    pkg-config \
    && rm -rf /var/lib/apt/lists/*

# Instalar la extensión a través de PECL, pero SIN forzar una compilación masiva si es posible
RUN pecl install mongodb && docker-php-ext-enable mongodb

# Habilitar mod_rewrite para Apache
RUN a2enmod rewrite

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copiar archivos
COPY . .

# Instalar dependencias de PHP
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Ajustar permisos
RUN chown -R www-data:www-data /var/www/html
