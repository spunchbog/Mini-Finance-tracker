FROM php:8.2-apache

# Enable mysqli/pdo_mysql for MySQL connection
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy project files into Apache's web root
COPY . /var/www/html/

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Apache config: allow .htaccess overrides if you use them
RUN a2enmod rewrite

EXPOSE 80