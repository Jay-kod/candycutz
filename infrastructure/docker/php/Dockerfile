# Production PHP 8.2-FPM Alpine Container for Candycutz
FROM php:8.2-fpm-alpine

# Set working directory
WORKDIR /var/www/html

# Install build dependencies and system libraries
RUN apk add --no-cache \
    bash \
    curl \
    git \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    zip \
    unzip \
    oniguruma-dev \
    icu-dev \
    linux-headers \
    $PHPIZE_DEPS

# Configure and install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        bcmath \
        opcache \
        gd \
        exif \
        pcntl \
        zip \
        intl

# Install Redis extension via PECL
RUN pecl install redis \
    && docker-php-ext-enable redis \
    && apk del $PHPIZE_DEPS \
    && rm -rf /tmp/pear

# Install Composer from official image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy configuration files
COPY infrastructure/docker/php/php.ini /usr/local/etc/php/conf.d/99-custom.ini
COPY infrastructure/docker/php/opcache.ini /usr/local/etc/php/conf.d/99-opcache.ini

# Copy application source code
COPY barbing-saloon-api /var/www/html

# Install production dependencies and configure permissions
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Expose FastCGI port
EXPOSE 9000

USER www-data

CMD ["php-fpm"]
