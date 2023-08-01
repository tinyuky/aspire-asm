# Use the official PHP-FPM image as the base image
FROM php:7.4-fpm

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql

# Set the working directory to /var/www/html
WORKDIR /var/www/html

# Copy the composer.json and composer.lock files to the container
COPY composer.json composer.lock ./

# Install the project dependencies using Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer install --no-scripts

# Copy the rest of the project files to the container
COPY . .

# Set permissions for storage and bootstrap/cache directories
RUN chown -R www-data:www-data storage bootstrap/cache

# Copy the .env.example and rename it to .env
COPY .env.example .env

# Generate the application key
RUN php artisan key:generate

# Expose port 9000 (required for PHP-FPM)
EXPOSE 9000


# Run Laravel application
CMD php artisan serve --host=0.0.0.0 --port=8000
