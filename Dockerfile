FROM php:8.2-fpm

# system deps
RUN apt-get update && apt-get install -y \
    git unzip libpq-dev libonig-dev libzip-dev zip \
    && docker-php-ext-install pdo pdo_pgsql mbstring zip bcmath

# composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# install composer dependencies (if composer.json present)
# COPY composer.json composer.lock ./
# RUN composer install --no-dev --no-scripts --no-autoloader

# set permissions
RUN chown -R www-data:www-data /var/www/html || true

EXPOSE 9000

CMD ["php-fpm"]