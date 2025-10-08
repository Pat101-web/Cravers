# Use the official PHP 8.2 image with Apache
FROM php:8.2-apache

# Copy app files into the container
COPY . /var/www/html/ ./public

# Enable mod_rewrite for Laravel, etc. (optional)
RUN a2enmod rewrite

# Expose port 80
EXPOSE 80
