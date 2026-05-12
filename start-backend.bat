@echo off
chcp 65001 >nul 2>&1
title MPP Chatbot - Backend (Laravel)

echo ============================================
echo   MPP Chatbot - Backend (Laravel)
echo   http://localhost:8000
echo ============================================
echo.
echo Tekan Ctrl+C untuk menghentikan...
echo.

cd /d "%~dp0backend"

:: Cek apakah .env ada
if not exist .env (
    echo [ERROR] File .env belum ada! Jalankan setup.bat terlebih dahulu.
    pause
    exit /b 1
)

:: Cek apakah database.sqlite ada
if not exist database\database.sqlite (
    echo [WARNING] database.sqlite belum ada, membuat file...
    type nul > database\database.sqlite
    call php artisan migrate --seed --quiet 2>nul
)

:: Jalankan Laravel development server
php artisan serve --host=0.0.0.0 --port=8000

pause
