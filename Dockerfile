FROM composer:2 as build
WORKDIR /app
COPY . /app
RUN composer install --no-dev --optimize-autoloader --no-interaction

FROM php:8.4-fpm
WORKDIR /app
COPY --from=build /app /app
RUN docker-php-ext-install pdo pdo_pgsql
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache
CMD ["php-fpm"]
