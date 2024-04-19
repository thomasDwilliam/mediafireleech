# Use a PHP base image
FROM php:latest

# Update package lists and upgrade existing packages
RUN apt-get update && apt-get upgrade -y

# Install required PHP extensions and openssl
RUN apt-get install -y php-curl php-dom php-openssl python openssl

# Copy index.php and dlsend.php files to the container
COPY index.php /var/www/html/index.php
COPY dlsend.php /var/www/html/dlsend.php

# Generate a self-signed SSL certificate
RUN openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
    -keyout /etc/ssl/private/server.key -out /etc/ssl/certs/server.crt \
    -subj "/C=US/ST=CA/L=San Francisco/O=MyOrg/OU=MyUnit/CN=localhost"

# Expose port 8080 for the PHP webserver
EXPOSE 8080
# Expose port 8081 for the Telegram Bot API
EXPOSE 8081

# Start PHP server with HTTPS
CMD php -S 0.0.0.0:8080 -t /var/www/html/ \
    -d variables_order=EGPCS \
    -d extension=php_openssl.dll \
    -d extension=php_curl.dll \
    -d extension=php_dom.dll \
    -d extension=php_sockets.dll \
    -d extension=php_mbstring.dll & \
    docker run -d -p 8081:8081 --name=telegram-bot-api --restart=always -v telegram-bot-api-data:/var/lib/telegram-bot-api -e TELEGRAM_API_ID=7784110 -e TELEGRAM_API_HASH=f81b6478f985c1283fa8c4847d1860ec -e TELEGRAM_LOCAL=1 -e TELEGRAM_STAT=1 -p 8082:8082 aiogram/telegram-bot-api:latest
