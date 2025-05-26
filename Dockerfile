# Use official PHP image with Apache
FROM php:8.1-apache

# Install mysqli extension
RUN docker-php-ext-install mysqli

# Copy your project to the Apache web root
COPY . /var/www/html/

# Expose port 80
EXPOSE 80
