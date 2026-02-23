# Use a base PHP-FPM image
FROM php:8.3-fpm-alpine

# Install system dependencies and PHP extensions
RUN apk add --no-cache \
    build-base \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    icu-dev \
    postgresql-dev \
    sqlite-dev \
    git \
    unzip \
    zip \
    curl \
    nodejs \
    npm \
    && npm install -g chokidar@^3.5.3 \
    && docker-php-ext-install \
    pdo_sqlite \
    pdo_mysql \
    pdo_pgsql \
    gd \
    zip \
    intl \
    pcntl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && apk add --no-cache libstdc++ \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install swoole \
    && docker-php-ext-enable swoole \
    && apk del .build-deps

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html/backend

# Copy application files
COPY . .

# Install Node dependencies and Composer dependencies
RUN npm install \
    && composer install --no-scripts --optimize-autoloader

# Set permissions for Laravel storage and bootstrap/cache
RUN chown -R www-data:www-data storage bootstrap/cache database \
    && chmod -R 775 storage bootstrap/cache \
    && chmod -R 775 database

# Expose port 8000 for Octane
EXPOSE 8000

# Start Octane
CMD ["php", "artisan", "octane:start", "--server=swoole", "--host=0.0.0.0", "--port=8000", "--watch"]

