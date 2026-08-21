FROM php:8.2-apache

# 1. Installation des dépendances système requises par les extensions PHP
RUN apt-get update && apt-get install -y \
    libicu-dev \
    libzip-dev \
    libpng-dev \
    libcurl4-openssl-dev \
    libonig-dev \
    unzip \
    git \
    && rm -rf /var/lib/apt/lists/*

# 2. Installation des extensions PHP pour CodeIgniter 4
RUN docker-php-ext-configure intl \
    && docker-php-ext-install intl mysqli pdo pdo_mysql zip gd mbstring curl

# 3. Activation de l'URL Rewriting d'Apache
RUN a2enmod rewrite

RUN echo "output_buffering = 4096" > /usr/local/etc/php/conf.d/output-buffering.ini

# 4. Ciblage du dossier public de CodeIgniter
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 5. Installation de Composer directement dans le conteneur
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer