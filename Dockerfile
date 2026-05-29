FROM php:8.2-apache

# Instalar dependencias necesarias para compilar extensiones y otras herramientas
RUN apt-get update && apt-get install -y \
    libssl-dev \
    libpcre3-dev \
    libcurl4-openssl-dev \
    pkg-config \
    git \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Instalar extensión MongoDB
# Usar 'pecl install' a veces falla por memoria; 
# el driver oficial de PHP recomienda esto:
RUN pecl install mongodb \
    && docker-php-ext-enable mongodb

# (El resto de tu archivo sigue igual)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN a2enmod rewrite
WORKDIR /var/www/html
COPY . .
RUN composer install --no-dev --optimize-autoloader --no-interaction
RUN chown -R www-data:www-data /var/www/html
EXPOSE 80
