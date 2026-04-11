FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    libicu-dev \
    default-libmysqlclient-dev \
    && docker-php-ext-install pdo pdo_mysql intl opcache

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
