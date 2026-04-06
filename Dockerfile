FROM php:8.2-apache

# Install OS packages & PHP extensions
RUN apt-get update && apt-get install -y git unzip \
    && docker-php-ext-install mysqli pdo pdo_mysql

# Copy source code ke Apache root
COPY . /var/www/html/

# Set working directory
WORKDIR /var/www/html

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Require package via Composer
RUN /usr/bin/composer require --no-interaction firebase/php-jwt

# Optional: enable rewrite
RUN a2enmod rewrite

EXPOSE 80
CMD ["apache2-foreground"]