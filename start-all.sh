#!/bin/bash

# ============================================================
# MPP Chatbot Kab. Bengkayang - Start All Services
# ============================================================
# Menjalankan Backend, Frontend, dan Bot secara bersamaan
# Tekan Ctrl+C untuk menghentikan semua service
# ============================================================

set -e

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
CYAN='\033[0;36m'
NC='\033[0m'

# Store PIDs for cleanup
PIDS=()

cleanup() {
    echo ""
    echo -e "${YELLOW}Menghentikan semua service...${NC}"
    for pid in "${PIDS[@]}"; do
        if kill -0 "$pid" 2>/dev/null; then
            kill "$pid" 2>/dev/null
            wait "$pid" 2>/dev/null
        fi
    done
    echo -e "${GREEN}Semua service dihentikan.${NC}"
    exit 0
}

trap cleanup SIGINT SIGTERM

# Get script directory
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

echo -e "${CYAN}============================================${NC}"
echo -e "${CYAN}  MPP Chatbot - Starting All Services${NC}"
echo -e "${CYAN}============================================${NC}"
echo ""

# ============================================================
# Start Backend (Laravel)
# ============================================================
echo -e "${GREEN}[1/3] Starting Backend (Laravel) on port 8000...${NC}"
cd "$SCRIPT_DIR/backend"
php artisan serve --host=0.0.0.0 --port=8000 > /tmp/mpp-backend.log 2>&1 &
PIDS+=($!)
echo -e "  PID: ${PIDS[-1]} | Log: /tmp/mpp-backend.log"

# Wait a moment for backend to start
sleep 2

# ============================================================
# Start Frontend (Vue + Vite)
# ============================================================
echo -e "${GREEN}[2/3] Starting Frontend (Vue) on port 3000...${NC}"
cd "$SCRIPT_DIR/frontend"
npx vite --port 3000 --host > /tmp/mpp-frontend.log 2>&1 &
PIDS+=($!)
echo -e "  PID: ${PIDS[-1]} | Log: /tmp/mpp-frontend.log"

# ============================================================
# Start Bot (Go)
# ============================================================
echo -e "${GREEN}[3/3] Starting WhatsApp Bot on port 8080...${NC}"
cd "$SCRIPT_DIR/bot"
go run . > /tmp/mpp-bot.log 2>&1 &
PIDS+=($!)
echo -e "  PID: ${PIDS[-1]} | Log: /tmp/mpp-bot.log"

echo ""
echo -e "${CYAN}============================================${NC}"
echo -e "${CYAN}  Semua service sudah berjalan! 🚀${NC}"
echo -e "${CYAN}============================================${NC}"
echo ""
echo -e "  ${GREEN}Dashboard${NC}  : http://localhost:3000"
echo -e "  ${GREEN}API${NC}        : http://localhost:8000/api"
echo -e "  ${GREEN}Bot Webhook${NC}: http://localhost:8080"
echo ""
echo -e "  Login: admin@mpp-bengkayang.go.id / password123"
echo ""
echo -e "${YELLOW}Tekan Ctrl+C untuk menghentikan semua service${NC}"
echo ""
echo -e "Melihat log:"
echo -e "  tail -f /tmp/mpp-backend.log"
echo -e "  tail -f /tmp/mpp-frontend.log"
echo -e "  tail -f /tmp/mpp-bot.log"
echo ""

# Wait for any child process to exit
wait
