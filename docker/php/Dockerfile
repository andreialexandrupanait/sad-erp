FROM php:8.3-fpm

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    zip \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libicu-dev \
    libzip-dev \
    libonig-dev \
    mariadb-client \
    supervisor \
    # Chromium for PDF generation (Spatie Laravel PDF / Browsershot)
    chromium \
    # Node.js for Browsershot
    nodejs \
    npm \
    # Fonts for proper PDF rendering
    fonts-liberation \
    fonts-dejavu-core \
    fonts-noto \
    && rm -rf /var/lib/apt/lists/*

# Set Chromium path for Browsershot
ENV PUPPETEER_EXECUTABLE_PATH=/usr/bin/chromium
ENV PUPPETEER_SKIP_CHROMIUM_DOWNLOAD=true

# Install Puppeteer globally for Browsershot (skip Chromium download since we have system Chromium)
RUN npm install -g puppeteer

# Configure and install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl

# Install Redis PHP extension
RUN pecl install redis && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# PHP config
COPY php.ini /usr/local/etc/php/conf.d/custom.ini

CMD ["php-fpm"]
