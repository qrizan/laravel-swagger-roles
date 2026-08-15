########################################
# Stage: vendor — install PHP dependencies, run Laravel package discovery
########################################
FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
        --no-dev \
        --no-scripts \
        --no-autoloader \
        --no-interaction \
        --prefer-dist

COPY . .

RUN composer dump-autoload --optimize --no-dev \
    && composer run-script post-autoload-dump --no-dev

########################################
# Target: php-fpm — application runtime
########################################
FROM php:8.3-fpm-alpine AS php-fpm

RUN apk add --no-cache libzip icu-libs \
    && apk add --no-cache --virtual .build-deps libzip-dev icu-dev \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql bcmath zip opcache \
    && apk del .build-deps

COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

WORKDIR /var/www/html

COPY --from=vendor --chown=www-data:www-data /app /var/www/html

RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

USER www-data

EXPOSE 9000

CMD ["php-fpm"]

########################################
# Target: nginx — serves public/, proxies *.php to the php-fpm target
########################################
FROM nginx:alpine AS nginx

COPY --from=vendor /app/public /var/www/html/public
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf

EXPOSE 80
