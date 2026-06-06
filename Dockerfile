FROM php:8.2-apache

# Install ekstensi PDO MySQL
RUN docker-php-ext-install pdo pdo_mysql

# Enable mod_rewrite
RUN a2enmod rewrite

# Copy semua file public ke web root
COPY public/ /var/www/html/
COPY src/ /var/www/src/

# Set permissions
RUN chown -R www-data:www-data /var/www/html /var/www/src

EXPOSE 80
