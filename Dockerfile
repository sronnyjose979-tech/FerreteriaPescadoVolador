# Etapa 1: preparar Laravel y sus dependencias
FROM serversideup/php:8.5-fpm-nginx-alpine AS preparacion

WORKDIR /var/www/html

COPY --chown=www-data:www-data composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --no-interaction \
    --prefer-dist

COPY --chown=www-data:www-data . .

RUN composer dump-autoload --optimize --no-dev --no-scripts \
    && php artisan package:discover --ansi


# Etapa 2: ejecutar la aplicación
FROM serversideup/php:8.5-fpm-nginx-alpine AS produccion

ENV PHP_OPCACHE_ENABLE=1 \
    AUTORUN_ENABLED=true

WORKDIR /var/www/html

COPY --from=preparacion --chown=www-data:www-data \
    /var/www/html /var/www/html

USER www-data

EXPOSE 8080