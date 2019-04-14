FROM php:7.1-fpm

RUN pecl install xdebug-2.7.1 && docker-php-ext-enable xdebug

COPY xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

RUN apt-get update && apt-get install -y libpq-dev && docker-php-ext-install pdo pdo_pgsql