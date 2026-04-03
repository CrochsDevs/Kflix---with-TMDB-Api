FROM php:8.2-apache

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Install pdo_mysql using pre-installed libraries
RUN docker-php-ext-install pdo pdo_mysql

WORKDIR /var/www/html

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy app files
COPY . ./

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Create cache dir and set permissions
RUN mkdir -p cache && chown -R www-data:www-data .

EXPOSE 80
