# Use a PHP base image
FROM php:latest

# Update package lists and upgrade existing packages
RUN apt-get update && apt-get upgrade -y

# Install PHP DOM and cURL extensions
RUN apt-get install -y libxml2-dev libcurl4-openssl-dev \
    && docker-php-ext-install dom curl

# Copy index.php and dlsend.php files to the container
COPY index.php /var/www/html/index.php
COPY dlsend.php /var/www/html/dlsend.php

# Expose port 8080 for the PHP webserver
EXPOSE 8080
# Expose port 8081 for the Telegram Bot API
EXPOSE 8081

# Start PHP server
CMD php -S 0.0.0.0:443 -t /var/www/html/ 
