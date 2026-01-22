FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libwebp-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install gd pdo pdo_mysql mysqli zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copiar configuración PHP personalizada para permitir uploads grandes
COPY custom.ini /usr/local/etc/php/conf.d/custom.ini

# Asegurar que www-data tenga permisos correctos
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www

WORKDIR /var/www/html
