FROM php:8.2-apache

# Install mysqli extension
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copy semua file ke apache root
COPY . /var/www/html/

# Expose port
EXPOSE 80
