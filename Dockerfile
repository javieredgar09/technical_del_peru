# Use official PHP image with Apache
FROM php:8.2-apache

# Set working directory
WORKDIR /var/www/html

# Install system dependencies required for Composer and PHP extensions
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    curl \
    libzip-dev \
    zlib1g-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    ca-certificates \
    && rm -rf /var/lib/apt/lists/*

# Install required PHP extensions
RUN docker-php-ext-configure zip --with-libzip && \
    docker-php-ext-install pdo pdo_mysql mysqli zip && \
    docker-php-ext-enable pdo pdo_mysql mysqli zip

# Enable Apache rewrite module
RUN a2enmod rewrite

# Copy project files
COPY . /var/www/html/

# Create necessary directories with proper permissions
RUN mkdir -p /var/www/html/public/assets/uploads && \
    mkdir -p /var/www/html/public/assets/uploads/qrcodes && \
    mkdir -p /var/www/html/public/assets/uploads/firmas && \
    mkdir -p /var/www/html/public/assets/uploads/modelos_3d

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Allow Composer to run as root inside the container
ENV COMPOSER_ALLOW_SUPERUSER=1

# Install PHP dependencies (prefer dist). If prefer-dist fails, try prefer-source.
RUN composer install --prefer-dist --no-dev --optimize-autoloader --no-interaction || \
    (echo "Composer dist install failed, retrying with source" && composer install --prefer-source --no-dev --optimize-autoloader --no-interaction)

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html && \
    chmod -R 777 /var/www/html/public/assets/uploads

# Configure Apache Document Root
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# Add Apache configuration for the public directory
RUN echo '<Directory /var/www/html/public>\\n    Options Indexes FollowSymLinks\\n    AllowOverride All\\n    Require all granted\\n</Directory>' >> /etc/apache2/apache2.conf

# Expose port 8080 for Railway
EXPOSE 8080

# Start Apache
CMD ["apache2ctl", "-D", "FOREGROUND"]

