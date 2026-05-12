@echo off
chcp 65001 >nul 2>&1
title MPP Chatbot - Backend (Laravel)

echo ============================================
echo   MPP Chatbot - Backend (Laravel)
echo   http://localhost:8000
echo ============================================
echo.

cd /d "%~dp0backend"

:: Cek apakah .env ada
if not exist .env (
    echo [!] File .env belum ada!
    echo     Membuat dari .env.example...
    copy .env.example .env >nul
    echo [OK] .env dibuat
    echo.
)

:: Cek apakah vendor ada (composer install sudah jalan)
if not exist vendor\NUL (
    echo [!] Folder vendor belum ada!
    echo     Menjalankan composer install...
    echo     Pastikan koneksi internet aktif.
    echo.
    call composer install --no-interaction
    if %ERRORLEVEL% NEQ 0 (
        echo.
        echo [ERROR] composer install gagal!
        echo Pastikan:
        echo   1. Composer sudah terinstall (composer -V)
        echo   2. Koneksi internet aktif
        echo   3. Jalankan setup.bat terlebih dahulu
        echo.
        pause
        exit /b 1
    )
    echo.
    echo [OK] Composer dependencies installed!
    echo.
    
    :: Generate key jika baru pertama kali
    call php artisan key:generate
)

:: Cek apakah database.sqlite ada
if not exist database\database.sqlite (
    echo [!] Database belum ada, membuat...
    if not exist database\NUL mkdir database
    type nul > database\database.sqlite
    call php artisan migrate --force
    call php artisan db:seed --force
    echo [OK] Database siap!
    echo.
)

echo Tekan Ctrl+C untuk menghentikan...
echo.

:: Jalankan Laravel development server
php artisan serve --host=0.0.0.0 --port=8000

pause
