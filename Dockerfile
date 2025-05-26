# Use PHP with Apache
FROM php:8.1-apache

# Enable mysqli
RUN docker-php-ext-install mysqli

# Copy your app to Apache web root
COPY . /var/www/html/

# Expose port 80
EXPOSE 80
