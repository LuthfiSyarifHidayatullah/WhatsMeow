@echo off
chcp 65001 >nul 2>&1

echo ============================================
echo   Diskominfo Chatbot - Start All Services
echo ============================================
echo.

:: Cek apakah sudah di-setup
if not exist "%~dp0backend\vendor\autoload.php" (
    echo [!] Setup belum dijalankan!
    echo     Jalankan setup.bat terlebih dahulu.
    echo.
    pause
    exit /b 1
)

echo [1/4] Starting Backend (Laravel) - port 8000
start "Diskominfo-Backend" cmd /k "cd /d "%~dp0backend" && php artisan serve --host=0.0.0.0 --port=8000"

timeout /t 3 /nobreak >nul

echo [2/4] Starting Scheduler (Auto-timeout) - cek setiap 1 menit
start "Diskominfo-Scheduler" cmd /k "cd /d "%~dp0backend" && php artisan schedule:work"

timeout /t 2 /nobreak >nul

echo [3/4] Starting Frontend (Vue) - port 3000
start "Diskominfo-Frontend" cmd /k "cd /d "%~dp0frontend" && npx vite --port 3000 --host"

timeout /t 2 /nobreak >nul

echo [4/4] Starting Bot (Go) - port 8080
start "Diskominfo-Bot" cmd /k "cd /d "%~dp0bot" && go run ."

echo.
echo ============================================
echo   SEMUA SERVICE BERJALAN!
echo ============================================
echo.
echo   Backend   : http://localhost:8000
echo   Frontend  : http://localhost:3000
echo   Bot       : http://localhost:8080
echo   Scheduler : Running (auto-timeout tiap 1 menit)
echo.
echo   Login: admin@mpp-bengkayang.go.id / password123
echo.
echo   Tutup window CMD untuk stop service.
echo ============================================
echo.

timeout /t 5 /nobreak >nul
start http://localhost:3000

pause
