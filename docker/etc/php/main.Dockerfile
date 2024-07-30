FROM php:8.2-fpm

# Install dependencies
# -q flag is used to reduce the output of apt-get, is stands for quiet
# -y flag is used to automatically answer yes to prompts is stands for yes
# libpng-dev is required for the gd extension, which is required by symfony
# intl is required for symfony
# xdebug is required for debugging
# pdo is required for database connections
# pdo_mysql is required for mysql connections
# libzip-dev is required for zip extension
# libicu-dev is required for intl extension
# zip is required for composer
# git is requried for grumphp
RUN apt-get -q update && apt-get -qy install \
    zip \
    cron \
    libpng-dev \
    libzip-dev \
    libicu-dev \
    git \
    && docker-php-ext-install intl opcache pdo pdo_mysql \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug

WORKDIR /var/www/html

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN curl -sS https://get.symfony.com/cli/installer | bash

COPY . /var/www/html

# Copy PHP-CS-Fixer configuration
COPY .php-cs-fixer.php /var/www/html/.php-cs-fixer.php

## ssh keys for repository access
#RUN mkdir -p /var/www/.ssh
#COPY docker/keys/* /var/www/.ssh/*

RUN ./vendor/bin/grumphp git:init || true

