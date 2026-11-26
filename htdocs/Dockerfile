<<<<<<< HEAD
=======
FROM php:8.4-apache
>>>>>>> parent of 986f7f3 (okk)

FROM php:8.4-apache

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
ENV PORT=10000

# Dépendances système
RUN apt-get update && apt-get install -y \
    libicu-dev \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libonig-dev \
    libcurl4-openssl-dev \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Extensions PHP
RUN docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg \
    && docker-php-ext-install \
    intl \
    mysqli \
    pdo \
    pdo_mysql \
    zip \
    gd \
    mbstring \
    curl \
    opcache

# Apache
RUN a2enmod rewrite headers expires deflate

RUN sed -ri \
    's!/var/www/html!/var/www/html/public!g' \
    /etc/apache2/sites-available/*.conf

RUN sed -ri \
    's/Listen 80/Listen 10000/g' \
    /etc/apache2/ports.conf

RUN sed -ri \
    's/<VirtualHost \*:80>/<VirtualHost *:10000>/g' \
    /etc/apache2/sites-available/*.conf

RUN printf '%s\n' \
    '<Directory /var/www/html/public>' \
    '    AllowOverride All' \
    '    Require all granted' \
    '</Directory>' \
    > /etc/apache2/conf-available/summury.conf \
    && a2enconf summury

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Dépendances PHP
COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader

# Code source
COPY . .

# Droits d'accès
RUN mkdir -p writable/cache writable/logs writable/session writable/uploads \
    && chown -R www-data:www-data writable \
    && chmod -R 775 writable

# OPcache
RUN docker-php-ext-enable opcache \
    && printf '%s\n' \
    'opcache.enable=1' \
    'opcache.memory_consumption=128' \
    'opcache.validate_timestamps=1' \
    'opcache.revalidate_freq=2' \
    > /usr/local/etc/php/conf.d/opcache-custom.ini

EXPOSE 10000
