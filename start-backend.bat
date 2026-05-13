@echo off
chcp 65001 >nul 2>&1
title MPP Backend - Laravel (port 8000)

cd /d "%~dp0backend"

if not exist vendor\autoload.php (
    echo [!] vendor belum ada, jalankan setup.bat dulu!
    pause
    exit /b 1
)

if not exist .env (
    copy .env.example .env >nul
    php artisan key:generate
)

if not exist database\database.sqlite (
    type nul > database\database.sqlite
    php artisan migrate --seed --force
)

echo.
echo   MPP Backend running di http://localhost:8000
echo   Tekan Ctrl+C untuk stop
echo.

php artisan serve --host=0.0.0.0 --port=8000
pause
