FROM php:8.2-apache

# Pastikan MPM yang dipakai cuma prefork
RUN a2dismod mpm_event || true
RUN a2enmod mpm_prefork || true

# Install mysqli dan PDO
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copy source code
COPY . /var/www/html/

# Enable rewrite kalau perlu
RUN a2enmod rewrite

EXPOSE 80
CMD ["apache2-foreground"]