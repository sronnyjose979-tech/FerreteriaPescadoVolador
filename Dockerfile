FROM serversideup/php:8.4-fpm-nginx
ENV PHP_OPCACHE_ENABLE=1 AUTORUN_ENABLED=true
WORKDIR /var/www/html
# Dependencias primero: aprovecha la caché de capas de Docker
COPY --chown=www-data:www-data composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --no-interaction --prefer-dist
# Código de la aplicación
COPY --chown=www-data:www-data . .
RUN composer dump-autoload --optimize --no-dev \
&& php artisan package:discover --ansi
USER www-data