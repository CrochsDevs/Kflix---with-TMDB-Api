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

# Create proper Laravel directories and write .env
RUN mkdir -p storage/framework/cache && \
    mkdir -p storage/framework/sessions && \
    mkdir -p storage/framework/views && \
    mkdir -p storage/logs && \
    mkdir -p bootstrap/cache && \
    printf 'APP_NAME=KFLIX\nAPP_ENV=production\nAPP_KEY=base64:e9446fc20b1ede0433ee461263a41c9b0046bd30e75e263e5dcc6fcace68025e\nAPP_DEBUG=false\nAPP_URL=http://localhost\nDB_CONNECTION=mysql\nDB_HOST=db\nDB_DATABASE=kflix_db\nDB_USERNAME=kflix_user\nDB_PASSWORD=kflix_password\nCACHE_DRIVER=file\nSESSION_DRIVER=file\nQUEUE_CONNECTION=sync\n' > .env && \
    chmod -R 775 storage && \
    chmod -R 775 bootstrap && \
    chown -R www-data:www-data storage && \
    chown -R www-data:www-data bootstrap && \
    chown -R www-data:www-data public && \
    chown www-data:www-data .env

EXPOSE 80
