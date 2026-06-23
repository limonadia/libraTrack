FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mysqli mbstring exif pcntl bcmath gd zip

# Enable Apache modules
RUN a2enmod rewrite headers

RUN apache2ctl -M | grep mpm

# Fix MPM conflict: disable event/worker, use only prefork
RUN a2dismod mpm_event mpm_worker || true \
 && a2enmod mpm_prefork \
 && rm -f /etc/apache2/mods-enabled/mpm_event.load \
 && rm -f /etc/apache2/mods-enabled/mpm_event.conf \
 && rm -f /etc/apache2/mods-enabled/mpm_worker.load \
 && rm -f /etc/apache2/mods-enabled/mpm_worker.conf

# Set working directory
WORKDIR /var/www/html

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy Composer files and install dependencies
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Copy backend files
COPY backend/ /var/www/html/

# Create Apache configuration that works with dynamic PORT
RUN echo '<VirtualHost *:${PORT}>\n\
    ServerAdmin webmaster@localhost\n\
    DocumentRoot /var/www/html\n\
    \n\
    <Directory /var/www/html>\n\
        Options Indexes FollowSymLinks\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
    \n\
    ErrorLog ${APACHE_LOG_DIR}/error.log\n\
    CustomLog ${APACHE_LOG_DIR}/access.log combined\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

# Update ports.conf to use PORT variable
RUN echo 'Listen ${PORT}' > /etc/apache2/ports.conf

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# ServerName to avoid warnings
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Startup script — also cleans MPM at runtime just in case
RUN printf '#!/bin/bash\n\
export PORT=${PORT:-8080}\n\
rm -f /etc/apache2/mods-enabled/mpm_event.load\n\
rm -f /etc/apache2/mods-enabled/mpm_event.conf\n\
rm -f /etc/apache2/mods-enabled/mpm_worker.load\n\
rm -f /etc/apache2/mods-enabled/mpm_worker.conf\n\
echo "Listen $PORT" > /etc/apache2/ports.conf\n\
sed -i "s/\\${PORT}/$PORT/g" /etc/apache2/sites-available/000-default.conf\n\
apache2-foreground\n' > /start.sh && chmod +x /start.sh

EXPOSE ${PORT:-8080}

CMD ["/start.sh"]