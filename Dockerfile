# Use official PHP image with Apache
FROM php:8.2-apache

# Enable Apache modules
RUN a2enmod rewrite
RUN a2enmod headers

# Install PHP extensions for MySQL and others
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . /var/www/html

# Set document root to public folder
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/apache2.conf

# Install PHP dependencies via Composer
RUN composer install --no-dev --optimize-autoloader

# Set permissions
RUN chown -R www-data:www-data /var/www/html
RUN find /var/www/html -type d -exec chmod 755 {} \;
RUN find /var/www/html -type f -exec chmod 644 {} \;

# Make uploads directory writable
RUN chmod -R 777 /var/www/html/public/assets/uploads

# Expose port
EXPOSE 8080

# Start Apache
CMD ["apache2ctl", "-D", "FOREGROUND"]
