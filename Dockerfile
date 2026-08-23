########################################
# Stage: vendor — install PHP dependencies, run Laravel package discovery
########################################
FROM composer:2@sha256:4d71c3c2109c61d5415544264b59ad4087e4c5b7244481723664138fd36d5040 AS vendor

WORKDIR /app

# Token GitHub opsional untuk composer: menghindari rate limit unduhan dist
# anonim (60/jam) dari codeload.github.com, yang mulai kena sejak build
# dipicu otomatis oleh CD tiap merge ke main, bukan cuma manual sesekali.
ARG COMPOSER_AUTH

COPY composer.json composer.lock ./
RUN COMPOSER_AUTH="${COMPOSER_AUTH}" composer install \
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
FROM php:8.3-fpm-alpine@sha256:bf90236449d333cef008b1f01c72a3d4f11a6470a74629665e4c6b6158f03fc8 AS php-fpm

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
FROM nginx:alpine@sha256:db35bfc6b2951e7f8a72db5db120288c127ffaeeb4a6d4b95a26fead017d5913 AS nginx

COPY --from=vendor /app/public /var/www/html/public
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf

EXPOSE 80
