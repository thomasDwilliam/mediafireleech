#!/bin/bash

# Install required packages
apk add php php-curl php-dom php-openssl python3 ffmpeg

# Copy PHP files to destination
cp index.php /root/index.php
cp dlsend.php /root/dlsend.php

# Navigate to the appropriate directory
cd ..
pwd
# Download and install ngrok


# Set up Telegram bot token
read -p "Enter telegram bot token: " bot_token
export botToken="$bot_token"

# Start Docker container for Telegram bot API
docker run -d -p 8081:8081 --name=telegram-bot-api --restart=always -v telegram-bot-api-data:/var/lib/telegram-bot-api -e TELEGRAM_API_ID=7784110 -e TELEGRAM_API_HASH=f81b6478f985c1283fa8c4847d1860ec -e TELEGRAM_LOCAL=1 -e TELEGRAM_STAT=1 -p 8082:8082 aiogram/telegram-bot-api:latest


# Set webhook for Telegram bot

# Sleep for a few seconds to allow Docker container to initialize
sleep 5
curl "http://localhost:8081/bot$botToken/setWebhook?url=http://localhost:8080/"
sleep 5
# Start PHP server and ngrok
php -S localhost:8080 & 
