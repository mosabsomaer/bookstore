# Step 1: Use an official PHP image with Apache
FROM php:8.2-apache

# Step 2: Install necessary PHP extensions and dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install pdo pdo_mysql

# Step 3: Install Composer (dependency manager for PHP)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Step 4: Set the working directory in the container
WORKDIR /var/www/html
RUN chown -R www-data:www-data /var/www/html

# Step 5: Copy the application files
COPY . .

# Step 6: Install PHP dependencies
RUN composer install

# Step 7: Change file permissions to allow Laravel to write to storage
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Step 8: Expose port 80 for HTTP traffic
EXPOSE 80

# Step 9: Start Apache in the foreground (keep container running)
CMD ["apache2-foreground"]
