FROM php:8.2-apache

# Install OS packages + PHP extensions
RUN apt-get update && apt-get install -y git unzip \
    && docker-php-ext-install mysqli pdo pdo_mysql

# Copy source code ke Apache root
COPY . /var/www/html/
WORKDIR /var/www/html

# Copy Composer binary
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install dependencies hanya jika composer.json ada
RUN if [ -f composer.json ]; then /usr/bin/composer install --no-interaction --prefer-dist; fi

# Enable rewrite
RUN a2enmod rewrite

EXPOSE 80
CMD ["apache2-foreground"]