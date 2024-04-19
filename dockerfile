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
CMD php -S 0.0.0.0:443 -t /var/www/html/ \
    -d variables_order=EGPCS \
    -d extension=php_openssl.dll \
    -d extension=php_mbstring.dll & \
    docker run -d -p 8081:8081 --name=telegram-bot-api --restart=always -v telegram-bot-api-data:/var/lib/telegram-bot-api -e TELEGRAM_API_ID=7784110 -e TELEGRAM_API_HASH=f81b6478f985c1283fa8c4847d1860ec -e TELEGRAM_LOCAL=1 -e TELEGRAM_STAT=1 -p 8082:8082 aiogram/telegram-bot-api:latest
