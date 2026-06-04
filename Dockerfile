FROM php:8.2-apache

# Instalar dependencias del sistema necesarias
RUN apt-get update && apt-get install -y \
    libssl-dev \
    pkg-config \
    git \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Instalar mongodb mediante PECL pero en una sola línea para evitar caché de memoria innecesaria
RUN pecl install mongodb && docker-php-ext-enable mongodb

# Habilitar mod_rewrite para tu app
RUN a2enmod rewrite

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copiar archivos
COPY . .

# Instalar dependencias de PHP (esto es lo importante)
# Si sigue fallando aquí, añade --no-dev
RUN composer install --no-dev --optimize-autoloader

# Ajustar permisos
RUN chown -R www-data:www-data /var/www/html
