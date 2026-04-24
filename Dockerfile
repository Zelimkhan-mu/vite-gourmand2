FROM php:8.4-apache

RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libicu-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath intl \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb \
    && a2enmod rewrite \
    && apt-get clean

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --no-interaction --optimize-autoloader

RUN sed -i 's|/var/www/html|/var/www/public|g' /etc/apache2/sites-available/000-default.conf \
    && printf '<Directory /var/www/public>\n    AllowOverride All\n</Directory>\n' >> /etc/apache2/apache2.conf

RUN chown -R www-data:www-data /var/www
