@echo off
chcp 65001 >nul 2>&1
setlocal

:: ============================================================
:: MPP Chatbot Kab. Bengkayang - Start All Services (Windows)
:: ============================================================
:: Membuka 3 window Command Prompt terpisah untuk setiap service.
:: Tutup masing-masing window untuk menghentikan service.
:: ============================================================

echo ============================================
echo   MPP Chatbot - Starting All Services
echo ============================================
echo.

:: ============================================================
:: Start Backend (new window)
:: ============================================================
echo [1/3] Starting Backend (Laravel) on http://localhost:8000 ...
start "MPP Backend - Laravel" cmd /k "cd /d "%~dp0backend" && php artisan serve --host=0.0.0.0 --port=8000"

:: Tunggu 3 detik agar backend siap terlebih dahulu
echo       Menunggu backend siap...
timeout /t 3 /nobreak >nul

:: ============================================================
:: Start Frontend (new window)
:: ============================================================
echo [2/3] Starting Frontend (Vue) on http://localhost:3000 ...
start "MPP Frontend - Vue" cmd /k "cd /d "%~dp0frontend" && npx vite --port 3000 --host"

:: ============================================================
:: Start Bot (new window)
:: ============================================================
echo [3/3] Starting WhatsApp Bot on http://localhost:8080 ...
start "MPP Bot - WhatsApp" cmd /k "cd /d "%~dp0bot" && go run ."

echo.
echo ============================================
echo   Semua service sudah dijalankan!
echo ============================================
echo.
echo   Dashboard  : http://localhost:3000
echo   API        : http://localhost:8000/api
echo   Bot Webhook: http://localhost:8080
echo.
echo   Login: admin@mpp-bengkayang.go.id / password123
echo.
echo   3 window baru sudah terbuka untuk masing-masing service.
echo   Tutup window tersebut untuk menghentikan service.
echo.
echo ============================================
echo.

pause
