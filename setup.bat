@echo off
chcp 65001 >nul 2>&1
setlocal EnableDelayedExpansion

echo.
echo ============================================
echo   MPP Chatbot Kab. Bengkayang - SETUP
echo ============================================
echo.
echo   PASTIKAN INTERNET AKTIF!
echo.

:: ============================================
:: CEK PHP
:: ============================================
where php >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo [X] PHP tidak ditemukan!
    echo     Download: https://windows.php.net/download
    echo     Pilih VS16 x64 Thread Safe, extract ke C:\php
    echo     Tambahkan C:\php ke System PATH
    pause
    exit /b 1
)
echo [OK] PHP ditemukan

:: CEK COMPOSER
where composer >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo [X] Composer tidak ditemukan!
    echo     Download: https://getcomposer.org/Composer-Setup.exe
    pause
    exit /b 1
)
echo [OK] Composer ditemukan

:: CEK NODE
where node >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo [X] Node.js tidak ditemukan!
    echo     Download: https://nodejs.org/
    pause
    exit /b 1
)
echo [OK] Node.js ditemukan

:: CEK GO
where go >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo [X] Go tidak ditemukan!
    echo     Download: https://go.dev/dl/
    pause
    exit /b 1
)
echo [OK] Go ditemukan

:: CEK PHP EXTENSIONS
echo.
echo --- Cek PHP Extensions ---
php -m 2>nul | findstr /i "pdo_sqlite" >nul
if %ERRORLEVEL% NEQ 0 (
    echo [X] pdo_sqlite TIDAK AKTIF!
    echo     Buka php.ini, cari ;extension=pdo_sqlite
    echo     Hapus tanda ; di depannya
    echo     Pastikan juga: extension_dir = "ext"
    pause
    exit /b 1
)
echo [OK] pdo_sqlite aktif

php -m 2>nul | findstr /i "mbstring" >nul
if %ERRORLEVEL% NEQ 0 (
    echo [X] mbstring belum aktif! Aktifkan di php.ini
    pause
    exit /b 1
)
echo [OK] mbstring aktif

php -m 2>nul | findstr /i "fileinfo" >nul
if %ERRORLEVEL% NEQ 0 (
    echo [X] fileinfo belum aktif! Aktifkan di php.ini
    pause
    exit /b 1
)
echo [OK] fileinfo aktif

echo.
echo ============================================
echo   SETUP BACKEND (Laravel)
echo ============================================
echo.

cd /d "%~dp0backend"

if not exist .env (
    copy .env.example .env >nul
    echo [OK] .env dibuat
)

if not exist database\NUL mkdir database
if not exist database\database.sqlite (
    type nul > database\database.sqlite
    echo [OK] database.sqlite dibuat
)

echo.
echo --- composer install (tunggu 1-3 menit) ---
call composer install --no-interaction
if %ERRORLEVEL% NEQ 0 (
    echo [X] COMPOSER INSTALL GAGAL!
    echo     Pastikan internet aktif
    pause
    exit /b 1
)
echo [OK] composer install selesai

:: Generate key
php artisan key:generate 2>nul
echo [OK] APP_KEY generated

:: Migrate
echo.
echo --- Migrasi database ---
php artisan migrate --force
if %ERRORLEVEL% NEQ 0 (
    echo [!] Migrasi gagal, coba reset...
    del database\database.sqlite 2>nul
    type nul > database\database.sqlite
    php artisan migrate --force
)
echo [OK] Database migrated

:: Seed
php artisan db:seed --force
echo [OK] Database seeded

cd /d "%~dp0"

echo.
echo ============================================
echo   SETUP FRONTEND (Vue + Tailwind)
echo ============================================
echo.

cd /d "%~dp0frontend"

echo --- npm install (tunggu 1-3 menit) ---
call npm install
if %ERRORLEVEL% NEQ 0 (
    echo [X] NPM INSTALL GAGAL!
    echo     Pastikan internet aktif
    pause
    exit /b 1
)
echo [OK] npm install selesai

cd /d "%~dp0"

echo.
echo ============================================
echo   SETUP BOT (Go + WhatsMeow)
echo ============================================
echo.

cd /d "%~dp0bot"

if not exist .env (
    copy .env.example .env >nul
    echo [OK] .env bot dibuat
)

echo --- go get dependencies (tunggu 2-5 menit) ---
call go get go.mau.fi/whatsmeow@latest 2>&1
call go get github.com/mattn/go-sqlite3@latest 2>&1
call go get github.com/mdp/qrterminal/v3@latest 2>&1
call go get google.golang.org/protobuf@latest 2>&1
call go mod tidy 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo [X] GO MOD TIDY GAGAL!
    echo     Pastikan internet aktif
    pause
    exit /b 1
)
echo [OK] Go modules selesai

cd /d "%~dp0"

echo.
echo ============================================
echo.
echo   SETUP SELESAI!
echo.
echo   Jalankan: start-all.bat
echo.
echo   Dashboard : http://localhost:3000
echo   Login     : admin@mpp-bengkayang.go.id
echo   Password  : password123
echo.
echo ============================================
echo.
pause
