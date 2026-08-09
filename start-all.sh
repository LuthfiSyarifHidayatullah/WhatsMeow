#!/bin/bash

# ============================================================
# Diskominfo Chatbot Kab. Bengkayang - Start All Services
# ============================================================
# Menjalankan Backend, Frontend, Bot, dan Scheduler secara bersamaan
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
echo -e "${CYAN}  Diskominfo Chatbot - Starting All Services${NC}"
echo -e "${CYAN}============================================${NC}"
echo ""

# ============================================================
# Start Backend (Laravel)
# ============================================================
echo -e "${GREEN}[1/4] Starting Backend (Laravel) on port 8000...${NC}"
cd "$SCRIPT_DIR/backend"
php artisan serve --host=0.0.0.0 --port=8000 > /tmp/diskominfo-backend.log 2>&1 &
PIDS+=($!)
echo -e "  PID: ${PIDS[-1]} | Log: /tmp/diskominfo-backend.log"

# Wait a moment for backend to start
sleep 2

# ============================================================
# Start Scheduler (Auto-timeout)
# ============================================================
echo -e "${GREEN}[2/4] Starting Scheduler (auto-timeout tiap 1 menit)...${NC}"
cd "$SCRIPT_DIR/backend"
php artisan schedule:work > /tmp/diskominfo-scheduler.log 2>&1 &
PIDS+=($!)
echo -e "  PID: ${PIDS[-1]} | Log: /tmp/diskominfo-scheduler.log"

# ============================================================
# Start Frontend (Vue + Vite)
# ============================================================
echo -e "${GREEN}[3/4] Starting Frontend (Vue) on port 3000...${NC}"
cd "$SCRIPT_DIR/frontend"
npx vite --port 3000 --host > /tmp/diskominfo-frontend.log 2>&1 &
PIDS+=($!)
echo -e "  PID: ${PIDS[-1]} | Log: /tmp/diskominfo-frontend.log"

# ============================================================
# Start Bot (Go)
# ============================================================
echo -e "${GREEN}[4/4] Starting WhatsApp Bot on port 8080...${NC}"
cd "$SCRIPT_DIR/bot"
go run . > /tmp/diskominfo-bot.log 2>&1 &
PIDS+=($!)
echo -e "  PID: ${PIDS[-1]} | Log: /tmp/diskominfo-bot.log"

echo ""
echo -e "${CYAN}============================================${NC}"
echo -e "${CYAN}  Semua service sudah berjalan! 🚀${NC}"
echo -e "${CYAN}============================================${NC}"
echo ""
echo -e "  ${GREEN}Dashboard${NC}  : http://localhost:3000"
echo -e "  ${GREEN}API${NC}        : http://localhost:8000/api"
echo -e "  ${GREEN}Bot Webhook${NC}: http://localhost:8080"
echo -e "  ${GREEN}Scheduler${NC}  : Running (auto-timeout tiap 1 menit)"
echo ""
echo -e "  Login: admin@mpp-bengkayang.go.id / password123"
echo ""
echo -e "${YELLOW}Tekan Ctrl+C untuk menghentikan semua service${NC}"
echo ""
echo -e "Melihat log:"
echo -e "  tail -f /tmp/diskominfo-backend.log"
echo -e "  tail -f /tmp/diskominfo-frontend.log"
echo -e "  tail -f /tmp/diskominfo-bot.log"
echo -e "  tail -f /tmp/diskominfo-scheduler.log"
echo ""

# Wait for any child process to exit
wait
