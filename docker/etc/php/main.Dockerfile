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
# libjpeg-dev is required for gd extension and image processing
# libtiff-dev is required for gd extension and image processing
# libwebp-dev is required for gd extension and image processing
# libgif-dev is required for gd extension and image processing
# zip is required for composer
# git is requried for grumphp
# libxml2-dev is required for soap extension
# soap is for gusApi
RUN apt-get -q update && apt-get -qy install \
    zip \
    cron \
    libpng-dev \
    libzip-dev \
    libicu-dev \
    libjpeg-dev \
    libtiff-dev \
    libwebp-dev \
    libgif-dev \
    git \
    libxml2-dev \
    && pecl install pcov \
    && docker-php-ext-enable pcov \
    && docker-php-ext-install intl opcache pdo pdo_mysql soap \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug

WORKDIR /var/www/html

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN curl -sS https://get.symfony.com/cli/installer | bash

COPY . /var/www/html

COPY assets/dashboard-ui/authentication/assets/images/ /var/www/html/public/assets/dashboard-ui/authentication/assets/images/


# Copy configuration files
COPY .php-cs-fixer.php /var/www/html/.php-cs-fixer.php
COPY phpstan.neon /var/www/html/phpstan.neon

## ssh keys for repository access
#RUN mkdir -p /var/www/.ssh
#COPY docker/keys/* /var/www/.ssh/*

RUN ./vendor/bin/grumphp git:init || true

