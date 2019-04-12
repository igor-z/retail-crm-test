FROM php:7.1-fpm

RUN pecl install xdebug-2.7.1 \
    && docker-php-ext-enable xdebug