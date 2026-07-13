FROM php:8.1-apache

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Copy backend code
COPY backend /var/www/html/backend
COPY frontend /var/www/html/frontend

# Set working directory
WORKDIR /var/www/html

# Configure Apache to serve from frontend directory
RUN echo '<VirtualHost *:80>\n\
    DocumentRoot /var/www/html/frontend\n\
    <Directory /var/www/html/frontend>\n\
        Options Indexes FollowSymLinks\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

# Expose port
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]