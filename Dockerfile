# Use official PHP + Apache image
FROM php:8.2-apache

# Install required PHP extensions
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Enable Apache mod_rewrite (optional)
RUN a2enmod rewrite

# Copy project files
WORKDIR /var/www/html
COPY . .

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
