FROM php:8.4-fpm

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
    && apt-get clean

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY app/ .

RUN composer install --no-interaction --optimize-autoloader

RUN chown -R www-data:www-data /var/www
