#!/bin/bash

# ANSI escape code for green color
GREEN='\033[0;32m'
# ANSI escape code to reset color
NC='\033[0m'

# Install required packages
apk add php php-curl php-dom php-openssl python3 ffmpeg

# Copy PHP files to destination
cp index.php /root/index.php
cp dlsend.php /root/dlsend.php

# Change directory to the root directory
cd /root/

# Download and install ngrok
wget https://bin.equinox.io/c/bNyj1mQVY4c/ngrok-v3-stable-linux-amd64.tgz && \
tar -xvzf ngrok-v3-stable-linux-amd64.tgz -C /usr/local/bin && \
rm -rf ngrok-v3-stable-linux-amd64.tgz

# Set up Telegram bot token
read -p "Enter telegram bot token: " bot_token
export botToken="$bot_token"

# Set up ngrok authtoken
read -p "Enter ngrok token: " nToken
ngrok config add-authtoken $nToken

# Start Docker container for Telegram bot API
echo -e "${GREEN}Starting Docker container for Telegram bot API...${NC}"
docker run -d -p 8081:8081 --name=telegram-bot-api --restart=always \
    -v telegram-bot-api-data:/var/lib/telegram-bot-api \
    -e TELEGRAM_API_ID=7784110 \
    -e TELEGRAM_API_HASH=f81b6478f985c1283fa8c4847d1860ec \
    -e TELEGRAM_LOCAL=1 \
    -e TELEGRAM_STAT=1 \
    -p 8082:8082 aiogram/telegram-bot-api:latest

# Set ngrok static domain
read -p "Enter ngrok static domain: " domain

# Set webhook for Telegram bot
echo -e "${GREEN}Setting webhook for Telegram bot...${NC}"
curl "http://localhost:8081/bot$botToken/setWebhook?url=https://$domain/"
echo ""

# Sleep to allow Docker container to initialize
echo -e "${GREEN}Waiting for Docker container to initialize...${NC}"
sleep 10

# Start PHP server and ngrok
echo -e "${GREEN}Starting PHP server and ngrok...${NC}"
php -S localhost:8080 & \
ngrok http --domain=$domain http://localhost:8080
