# Stage 1: Install PHP dependencies using composer image
FROM composer:2 AS vendor

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-plugins --no-scripts

# Stage 2: Runtime with Apache
FROM php:8.2-apache

# Enable Apache mod_rewrite and install MySQL PDO
RUN a2enmod rewrite && \
    docker-php-ext-install pdo pdo_mysql

WORKDIR /var/www/html

# Copy composer-installed vendor from stage 1
COPY --from=vendor /app/vendor ./vendor

# Copy application files
COPY . ./

# Set permissions
RUN mkdir -p cache && chown -R www-data:www-data .

EXPOSE 80
