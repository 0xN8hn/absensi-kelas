FROM php:8.2-apache

# Install extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copy source code
COPY . /var/www/html/

# Optional: enable rewrite
RUN a2enmod rewrite

EXPOSE 80
CMD ["apache2-foreground"]