FROM php:8.3-apache

# Enable Apache mod_rewrite
RUN a2enmod rewrite && \
    docker-php-ext-install pdo pdo_mysql

# Install system deps for composer/guzzle
RUN apt-get update && apt-get install -y git unzip && \
    rm -rf /var/lib/apt/lists/*

# Set document root to public/
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Composer files
COPY composer.json .
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Copy all app files
COPY . .

# Create proper Laravel directories and set permissions
RUN mkdir -p storage/framework/cache && \
    mkdir -p storage/framework/sessions && \
    mkdir -p storage/framework/views && \
    mkdir -p storage/logs && \
    mkdir -p bootstrap/cache && \
    touch .env && \
    chmod -R 775 storage && \
    chmod -R 775 bootstrap && \
    chown -R www-data:www-data storage && \
    chown -R www-data:www-data bootstrap && \
    chown -R www-data:www-data public && \
    chown www-data:www-data .env

EXPOSE 80
