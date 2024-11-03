# docker/php/Dockerfile

# Use the official PHP image
FROM php:8.1-fpm

# Set working directory
WORKDIR /var/www/symfony

# Install dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libpq-dev \
    && docker-php-ext-install \
    intl \
    pdo \
    pdo_mysql

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application files
COPY . .

# Install PHP dependencies
RUN composer install --no-scripts --no-autoloader && composer dump-autoload --optimize

# Set file permissions for Symfony cache and log folders
RUN chown -R www-data:www-data var
RUN chmod -R 775 var

# Expose port 9000 for PHP-FPM
EXPOSE 9000

# Run the PHP-FPM server
CMD ["php-fpm"]
