# ==============================================================================
# Multi-stage Dockerfile for Laravel 12 + Inertia.js + Vue 3 Room Reservation
# ==============================================================================

# ------------------------------------------------------------------------------
# Stage 1: Frontend Asset Compilation (Vite + Tailwind + Vue 3)
# ------------------------------------------------------------------------------
FROM node:20-alpine AS frontend
WORKDIR /app

COPY package*.json ./
RUN npm install

COPY . .
RUN npm run build

# ------------------------------------------------------------------------------
# Stage 2: PHP-FPM Application Image
# ------------------------------------------------------------------------------
FROM php:8.2-fpm-alpine AS app

# Install system packages & build dependencies
RUN apk update && apk add --no-cache \
    curl \
    git \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    icu-dev \
    oniguruma-dev

# Configure and install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl

# Copy official Composer binary
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy dependency definitions and install production packages
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

# Copy full application code
COPY . .

# Copy compiled frontend assets from Stage 1
COPY --from=frontend /app/public/build ./public/build

# Set correct storage and bootstrap permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]

