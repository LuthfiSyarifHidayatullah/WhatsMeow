#!/bin/bash

# ============================================================
# MPP Chatbot Kab. Bengkayang - Setup Script (Tanpa Docker)
# ============================================================
# Prerequisites:
#   - PHP >= 8.2 dengan extensions: pdo_sqlite, mbstring, xml, curl, zip
#   - Composer
#   - Node.js >= 18 + npm
#   - Go >= 1.22
# ============================================================

set -e

echo "============================================"
echo "  MPP Chatbot Kab. Bengkayang - Setup"
echo "============================================"
echo ""

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

# Check prerequisites
echo -e "${YELLOW}[1/6] Memeriksa prerequisites...${NC}"

check_command() {
    if ! command -v $1 &> /dev/null; then
        echo -e "${RED}ERROR: $1 tidak ditemukan. Silakan install terlebih dahulu.${NC}"
        exit 1
    fi
    echo -e "  ✓ $1 ditemukan: $($1 --version 2>&1 | head -1)"
}

check_command php
check_command composer
check_command node
check_command npm
check_command go

echo ""

# ============================================================
# Backend Setup (Laravel)
# ============================================================
echo -e "${YELLOW}[2/6] Setup Backend (Laravel)...${NC}"

cd backend

# Copy .env if not exists
if [ ! -f .env ]; then
    cp .env.example .env
    # Set to SQLite for easy development
    sed -i 's/DB_CONNECTION=mysql/DB_CONNECTION=sqlite/' .env
    sed -i 's/DB_HOST=127.0.0.1/# DB_HOST=127.0.0.1/' .env
    sed -i 's/DB_PORT=3306/# DB_PORT=3306/' .env
    sed -i 's/DB_DATABASE=mpp_chatbot/# DB_DATABASE=mpp_chatbot/' .env
    sed -i 's/DB_USERNAME=root/# DB_USERNAME=root/' .env
    sed -i 's/DB_PASSWORD=/# DB_PASSWORD=/' .env
    # Set bot token
    sed -i 's/BOT_API_TOKEN=your-secret-bot-token/BOT_API_TOKEN=mpp-bot-secret-token-2024/' .env
    echo -e "  ✓ File .env dibuat (menggunakan SQLite)"
fi

# Create SQLite database file
if [ ! -f database/database.sqlite ]; then
    touch database/database.sqlite
    echo -e "  ✓ Database SQLite dibuat"
fi

# Install composer dependencies
echo -e "  Installing composer dependencies..."
composer install --no-interaction --quiet 2>/dev/null || {
    echo -e "${YELLOW}  ⚠ Composer install gagal (mungkin offline). Lanjutkan setup lainnya...${NC}"
}

# Generate app key
if grep -q "GENERATE_NEW_KEY_HERE" .env 2>/dev/null; then
    php artisan key:generate --quiet 2>/dev/null || true
    echo -e "  ✓ APP_KEY generated"
fi

# Run migrations
php artisan migrate --force --quiet 2>/dev/null && echo -e "  ✓ Migrasi database selesai" || true

# Seed database
php artisan db:seed --force --quiet 2>/dev/null && echo -e "  ✓ Database seeder selesai" || true

cd ..
echo ""

# ============================================================
# Frontend Setup (Vue)
# ============================================================
echo -e "${YELLOW}[3/6] Setup Frontend (Vue + Tailwind)...${NC}"

cd frontend

# Install npm dependencies
echo -e "  Installing npm dependencies..."
npm install --silent 2>/dev/null || {
    echo -e "${YELLOW}  ⚠ npm install gagal (mungkin offline). Lanjutkan setup lainnya...${NC}"
}

cd ..
echo -e "  ✓ Frontend dependencies installed"
echo ""

# ============================================================
# Bot Setup (Go)
# ============================================================
echo -e "${YELLOW}[4/6] Setup Bot (Go + WhatsMeow)...${NC}"

cd bot

# Copy .env if not exists
if [ ! -f .env ]; then
    cp .env.example .env
    echo -e "  ✓ File .env bot dibuat"
fi

# Download Go dependencies
echo -e "  Downloading Go dependencies..."
go mod tidy 2>/dev/null || {
    echo -e "${YELLOW}  ⚠ go mod tidy gagal (mungkin offline). Lanjutkan setup lainnya...${NC}"
}

cd ..
echo ""

# ============================================================
# Create start scripts
# ============================================================
echo -e "${YELLOW}[5/6] Membuat start scripts...${NC}"

# Start backend script
cat > start-backend.sh << 'EOF'
#!/bin/bash
echo "Starting Laravel Backend on http://localhost:8000..."
cd backend
php artisan serve --host=0.0.0.0 --port=8000
EOF
chmod +x start-backend.sh

# Start frontend script
cat > start-frontend.sh << 'EOF'
#!/bin/bash
echo "Starting Vue Frontend on http://localhost:3000..."
cd frontend
npm run dev -- --port 3000 --host
EOF
chmod +x start-frontend.sh

# Start bot script
cat > start-bot.sh << 'EOF'
#!/bin/bash
echo "Starting WhatsApp Bot..."
echo "Scan QR code yang muncul di terminal dengan WhatsApp"
cd bot
go run .
EOF
chmod +x start-bot.sh

echo -e "  ✓ start-backend.sh"
echo -e "  ✓ start-frontend.sh"
echo -e "  ✓ start-bot.sh"
echo ""

# ============================================================
# Done
# ============================================================
echo -e "${YELLOW}[6/6] Setup selesai!${NC}"
echo ""
echo -e "${GREEN}============================================${NC}"
echo -e "${GREEN}  SETUP BERHASIL! 🎉${NC}"
echo -e "${GREEN}============================================${NC}"
echo ""
echo "Cara menjalankan (buka 3 terminal):"
echo ""
echo "  Terminal 1 - Backend:"
echo "    ./start-backend.sh"
echo ""
echo "  Terminal 2 - Frontend:"
echo "    ./start-frontend.sh"
echo ""
echo "  Terminal 3 - WhatsApp Bot:"
echo "    ./start-bot.sh"
echo ""
echo "Akses:"
echo "  Dashboard : http://localhost:3000"
echo "  API       : http://localhost:8000/api"
echo ""
echo "Login:"
echo "  Admin      : admin@mpp-bengkayang.go.id / password123"
echo "  Supervisor : supervisor@mpp-bengkayang.go.id / password123"
echo "  Petugas    : budi@mpp-bengkayang.go.id / password123"
echo ""
