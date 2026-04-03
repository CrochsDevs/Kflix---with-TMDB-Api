# Stage 1: Install PHP dependencies using composer image
FROM composer:2 AS vendor

# Fix DNS inside build container (Proxmox LXC networking quirk)
RUN echo "nameserver 8.8.8.8" > /etc/resolv.conf

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-plugins --no-scripts

# Stage 2: Runtime with Apache
FROM php:8.2-apache

# Fix DNS inside build container
RUN echo "nameserver 8.8.8.8" > /etc/resolv.conf && \
    a2enmod rewrite && \
    docker-php-ext-install pdo pdo_mysql

WORKDIR /var/www/html

# Copy composer-installed vendor from stage 1
COPY --from=vendor /app/vendor ./vendor

# Copy application files
COPY . ./

# Set permissions
RUN mkdir -p cache && chown -R www-data:www-data .

EXPOSE 80
