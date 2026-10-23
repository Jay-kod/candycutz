# --- 1. PHP dependencies ---
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --ignore-platform-reqs
COPY . .
RUN composer dump-autoload --optimize --no-dev

# --- 2. Runtime (FrankenPHP: PHP 8.3 + Caddy) ---
FROM dunglas/frankenphp:1-php8.3
RUN install-php-extensions pdo_mysql pdo_pgsql bcmath intl zip gd opcache pcntl
WORKDIR /app
COPY --from=vendor /app /app
RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
 && chown -R www-data:www-data storage bootstrap/cache
COPY docker/start.sh /start.sh
RUN chmod +x /start.sh
CMD ["/start.sh"]
