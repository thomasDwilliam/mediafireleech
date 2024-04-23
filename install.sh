#!/bin/bash

# Install required packages
apk add php php-curl php-dom php-openssl python3 ffmpeg

# Copy PHP files to destination
cp index.php /root/index.php
cp dlsend.php /root/dlsend.php

# Navigate to the appropriate directory
cd ..

# Download and install ngrok
wget https://bin.equinox.io/c/bNyj1mQVY4c/ngrok-v3-stable-linux-amd64.tgz && \
tar -xvzf ngrok-v3-stable-linux-amd64.tgz -C /usr/local/bin && \
rm -rf ngrok-v3-stable-linux-amd64.tgz

# Set up ngrok authtoken
read -p "Enter your ngrok token: " token
ngrok config add-authtoken $token

# Set up Telegram bot token
read -p "Enter telegram bot token: " bot_token
export botToken="$bot_token"

# Start Docker container for Telegram bot API
docker run -d -p 8081:8081 --name=telegram-bot-api --restart=always \
    -v telegram-bot-api-data:/var/lib/telegram-bot-api \
    -e TELEGRAM_API_ID=7784110 \
    -e TELEGRAM_API_HASH=f81b6478f985c1283fa8c4847d1860ec \
    -e TELEGRAM_LOCAL=1 \
    -e TELEGRAM_STAT=1 \
    -p 8082:8082 aiogram/telegram-bot-api:latest

curl "http://172.17.0.2:8081/bot$botToken/setWebhook?url=https://factual-routinely-guppy.ngrok-free.app"
# Start PHP server and ngrok
php -S localhost:8080 --bind 0.0.0.0:8080 & \
ngrok http --domain=factual-routinely-guppy.ngrok-free.app http://localhost:8080 