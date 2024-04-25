#!/bin/bash

# ANSI escape code for green color
GREEN='\033[0;32m'
# ANSI escape code to reset color
NC='\033[0m'

# Function to install ngrok
install_ngrok() {
    wget https://bin.equinox.io/c/bNyj1mQVY4c/ngrok-v3-stable-linux-amd64.tgz && \
    tar -xvzf ngrok-v3-stable-linux-amd64.tgz -C /usr/local/bin && \
    rm -rf ngrok-v3-stable-linux-amd64.tgz
}

echo -e "${GREEN}Choose an option:${NC}"
echo -e "${GREEN}1. Install Telegram server${NC}"
echo -e "${GREEN}2. Install Telegram bot${NC}"
read -p "${GREEN}Enter your choice (1 or 2): ${NC}" option

case $option in 
    1)
        echo -e "${GREEN}Installing ngrok .... ${NC}"
        install_ngrok

        # Set up Telegram bot token
        read -p "Enter telegram bot token: " bot_token
        export botToken="$bot_token"

        # Set up ngrok authtoken
        read -p "Enter ngrok token: " nToken
        ngrok config add-authtoken $nToken

        # Start Docker container for Telegram bot API
        echo -e "${GREEN}Starting Telegram Server...${NC}"
        docker run -d -p 8081:8081 --name=telegram-bot-api --restart=always \
            -v telegram-bot-api-data:/var/lib/telegram-bot-api \
            -e TELEGRAM_API_ID=7784110 \
            -e TELEGRAM_API_HASH=f81b6478f985c1283fa8c4847d1860ec \
            -e TELEGRAM_LOCAL=1 \
            -e TELEGRAM_STAT=1 \
            aiogram/telegram-bot-api:latest
        sleep 5
        echo -e "${GREEN}Telegram server initialized...${NC}"
        # Set ngrok static domain
        read -p "${GREEN}Enter ngrok static domain: ${NC}" domain
        read -p "${GREEN}Enter Telegram bot Backend endpoint: ${NC}" endpoint
        # Set webhook for Telegram bot
        
        # Sleep to allow Docker container to initialize
        echo -e "${GREEN}Forwarding porting using ngrok ${NC}"

        echo -e "${GREEN}Telegram server started at https://$domain/ ${NC}"
        sleep 2

        ngrok http --domain=$domain http://localhost:8081 &
        echo -e "${GREEN}Setting webhook for Telegram bot...${NC}"
        curl "https://$domain/bot$botToken/setWebhook?url=https://$endpoint/"
        ;;
    2)
        apk add php php-curl php-dom php-openssl python3 ffmpeg

        # Download and install ngrok
        install_ngrok

        # Set up Telegram bot token
        read -p "Enter telegram bot token: " bot_token
        export botToken="$bot_token"

        # Set up ngrok authtoken
        read -p "Enter ngrok token: " nToken
        ngrok config add-authtoken $nToken

        read -p "${GREEN}Enter ngrok static domain: ${NC}" domain
        # Start PHP server and ngrok
        echo -e "${GREEN}Starting PHP server and Initializing Ngrok...${NC}"
        sleep 5
        echo -e "${GREEN}Telegram bot server started at https://$domain/${NC}"
        php -S localhost:8080 & \
        ngrok http --domain=$domain http://localhost:8080
        ;;
    *)
        echo "Invalid option. Please choose either 1 or 2."
        ;;
esac
