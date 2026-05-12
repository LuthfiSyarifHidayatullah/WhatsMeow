@echo off
chcp 65001 >nul 2>&1
setlocal

:: ============================================================
:: MPP Chatbot Kab. Bengkayang - Start All Services (Windows)
:: ============================================================
:: Membuka 3 window Command Prompt terpisah.
:: Tutup masing-masing window untuk menghentikan service.
:: ============================================================

echo ============================================
echo   MPP Chatbot - Starting All Services
echo ============================================
echo.

:: Cek apakah setup sudah dijalankan
if not exist "%~dp0backend\vendor\NUL" (
    echo [!] Setup belum dijalankan!
    echo     Jalankan setup.bat terlebih dahulu.
    echo.
    set /p RUNSETUP="Jalankan setup.bat sekarang? (Y/N): "
    if /i "!RUNSETUP!"=="Y" (
        call "%~dp0setup.bat"
    ) else (
        echo Dibatalkan. Jalankan setup.bat dulu lalu coba lagi.
        pause
        exit /b 1
    )
)

:: ============================================================
:: Start Backend (new window)
:: ============================================================
echo [1/3] Starting Backend (Laravel) ...
echo       URL: http://localhost:8000
start "MPP Backend - Laravel (port 8000)" cmd /k "cd /d "%~dp0" && start-backend.bat"

:: Tunggu 3 detik agar backend siap
timeout /t 3 /nobreak >nul

:: ============================================================
:: Start Frontend (new window)
:: ============================================================
echo [2/3] Starting Frontend (Vue) ...
echo       URL: http://localhost:3000
start "MPP Frontend - Vue (port 3000)" cmd /k "cd /d "%~dp0" && start-frontend.bat"

:: Tunggu 2 detik
timeout /t 2 /nobreak >nul

:: ============================================================
:: Start Bot (new window)
:: ============================================================
echo [3/3] Starting WhatsApp Bot ...
echo       URL: http://localhost:8080
start "MPP Bot - WhatsApp (port 8080)" cmd /k "cd /d "%~dp0" && start-bot.bat"

echo.
echo ============================================
echo   Semua service sudah dijalankan!
echo ============================================
echo.
echo   3 window CMD baru sudah terbuka:
echo.
echo   [Window 1] Backend  : http://localhost:8000
echo   [Window 2] Frontend : http://localhost:3000
echo   [Window 3] Bot      : http://localhost:8080
echo.
echo   Buka browser: http://localhost:3000
echo.
echo   Login:
echo     Email    : admin@mpp-bengkayang.go.id
echo     Password : password123
echo.
echo   Untuk menghentikan: tutup masing-masing window CMD.
echo.
echo ============================================
echo.

:: Buka browser otomatis setelah 5 detik
timeout /t 5 /nobreak >nul
start http://localhost:3000

pause
