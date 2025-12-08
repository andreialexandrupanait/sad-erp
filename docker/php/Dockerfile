# ============================================================================
# PHP 8.3 FPM Dockerfile for Laravel 10/11 ERP Application
# Base: Official PHP 8.3 FPM Alpine Image
# ============================================================================

FROM php:8.3-fpm-alpine

LABEL maintainer="DevOps Team"
LABEL description="PHP 8.3 FPM with Laravel dependencies"

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    oniguruma-dev \
    libzip-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    icu-dev \
    bash \
    mysql-client \
    supervisor

# Install PHP extensions required by Laravel
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
    pdo \
    pdo_mysql \
    mysqli \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    intl \
    opcache \
    xml \
    soap

# Install Redis extension
RUN apk add --no-cache pcre-dev $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis

# Install Composer globally
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create system user to run Composer and Artisan commands
RUN addgroup -g 1000 www && \
    adduser -D -u 1000 -G www www

# Set correct permissions for Laravel
RUN chown -R www:www /var/www/html

# Change current user to www
USER www

# Expose port 9000 for PHP-FPM
EXPOSE 9000

# Start PHP-FPM server
CMD ["php-fpm"]
