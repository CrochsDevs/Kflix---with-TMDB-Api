FROM php:8.3-apache

# Enable Apache mod_rewrite
RUN a2enmod rewrite && \
    docker-php-ext-install pdo pdo_mysql

# Install system deps for composer/guzzle
RUN apt-get update && apt-get install -y git unzip && \
    rm -rf /var/lib/apt/lists/*

# Set document root to public/
ENV APACHE_DOCUMENT_ROOT /var/www/html/public

# Set ServerName to suppress AH00558 warning
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Write Apache VirtualHost config with rewrites (NO .htaccess needed)
# This avoids the infinite redirect loop from having rewrites in both vhost AND .htaccess
RUN printf '%s\n' \
    '<VirtualHost *:80>' \
    '    DocumentRoot /var/www/html/public' \
    '    <Directory /var/www/html/public>' \
    '        AllowOverride None' \
    '        Require all granted' \
    '        RewriteEngine On' \
    '        RewriteCond %{REQUEST_FILENAME} !-d' \
    '        RewriteCond %{REQUEST_FILENAME} !-f' \
    '        RewriteRule ^ /index.php [L]' \
    '    </Directory>' \
    '</VirtualHost>' \
    > /etc/apache2/sites-available/000-default.conf

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
    mkdir -p public && \
    printf 'APP_NAME=KFLIX\nAPP_ENV=production\nAPP_KEY=base64:9JtWXX9fyk2lk59ichWBCKRLmrXKCHV+b+UYes5oP0c=\nAPP_DEBUG=true\nAPP_URL=http://localhost\nDB_CONNECTION=mysql\nDB_HOST=db\nDB_DATABASE=kflix_db\nDB_USERNAME=kflix_user\nDB_PASSWORD=kflix_password\nCACHE_DRIVER=file\nSESSION_DRIVER=file\nQUEUE_CONNECTION=sync\n' > .env && \
    chmod -R 775 storage && \
    chmod -R 775 bootstrap && \
    chown -R www-data:www-data storage && \
    chown -R www-data:www-data bootstrap && \
    chown -R www-data:www-data public && \
    chown www-data:www-data .env

EXPOSE 80
