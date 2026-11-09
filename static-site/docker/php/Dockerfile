# syntax=docker/dockerfile:1
# Dedicated PHP-FPM for the marketing site — NOT the Laravel app image.
FROM php:8.2-fpm-bookworm

RUN apt-get update && apt-get install -y --no-install-recommends \
    libgd-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" gd \
    && rm -rf /var/lib/apt/lists/*

# Securimage needs writable session + captcha temp under docroot plugins
RUN mkdir -p /var/www/html && chown -R www-data:www-data /var/www/html

COPY zz-env.conf /usr/local/etc/php-fpm.d/zz-env.conf

WORKDIR /var/www/html
