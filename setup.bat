@echo off
chcp 65001 >nul 2>&1
setlocal EnableDelayedExpansion

:: ============================================================
:: MPP Chatbot Kab. Bengkayang - Setup Script untuk Windows
:: ============================================================
:: PENTING: Jalankan script ini dengan koneksi internet aktif!
:: Script akan mengunduh semua dependencies yang diperlukan.
:: ============================================================

echo ============================================
echo   MPP Chatbot Kab. Bengkayang - Setup
echo   (Windows Edition)
echo ============================================
echo.
echo PASTIKAN KONEKSI INTERNET AKTIF!
echo.

:: ============================================================
:: [1/6] Cek Prerequisites
:: ============================================================
echo [1/6] Memeriksa prerequisites...
echo.

set "HAS_ERROR=0"

where php >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo   [X] PHP TIDAK DITEMUKAN!
    echo       Download: https://windows.php.net/download
    echo       Pilih: VS16 x64 Thread Safe ^(zip^)
    echo       Extract ke C:\php lalu tambahkan ke PATH
    set "HAS_ERROR=1"
) else (
    for /f "tokens=1,2 delims= " %%a in ('php -v 2^>nul') do (
        if "%%a"=="PHP" echo   [OK] PHP %%b
        goto :php_check_done
    )
    :php_check_done
)

where composer >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo   [X] COMPOSER TIDAK DITEMUKAN!
    echo       Download: https://getcomposer.org/Composer-Setup.exe
    set "HAS_ERROR=1"
) else (
    echo   [OK] Composer found
)

where node >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo   [X] NODE.JS TIDAK DITEMUKAN!
    echo       Download: https://nodejs.org/ ^(pilih LTS^)
    set "HAS_ERROR=1"
) else (
    for /f "tokens=*" %%i in ('node --version 2^>nul') do echo   [OK] Node.js %%i
)

where npm >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo   [X] NPM TIDAK DITEMUKAN!
    set "HAS_ERROR=1"
) else (
    for /f "tokens=*" %%i in ('npm --version 2^>nul') do echo   [OK] npm v%%i
)

where go >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo   [X] GO TIDAK DITEMUKAN!
    echo       Download: https://go.dev/dl/ ^(pilih .msi installer^)
    set "HAS_ERROR=1"
) else (
    for /f "tokens=*" %%i in ('go version 2^>nul') do echo   [OK] %%i
)

if "%HAS_ERROR%"=="1" (
    echo.
    echo ============================================
    echo   GAGAL: Ada software yang belum terinstall!
    echo ============================================
    echo.
    echo   Install semua software di atas, lalu buka
    echo   Command Prompt BARU dan jalankan setup.bat lagi.
    echo.
    pause
    exit /b 1
)

echo.
echo   Semua prerequisites OK!
echo.

:: ============================================================
:: [2/6] Cek PHP Extensions
:: ============================================================
echo [2/6] Memeriksa PHP extensions...
echo.

set "EXT_ERROR=0"

php -m 2>nul | findstr /i "pdo_sqlite" >nul
if %ERRORLEVEL% NEQ 0 (
    echo   [X] pdo_sqlite TIDAK AKTIF!
    set "EXT_ERROR=1"
) else (
    echo   [OK] pdo_sqlite
)

php -m 2>nul | findstr /i "mbstring" >nul
if %ERRORLEVEL% NEQ 0 (
    echo   [X] mbstring TIDAK AKTIF!
    set "EXT_ERROR=1"
) else (
    echo   [OK] mbstring
)

php -m 2>nul | findstr /i "curl" >nul
if %ERRORLEVEL% NEQ 0 (
    echo   [X] curl TIDAK AKTIF!
    set "EXT_ERROR=1"
) else (
    echo   [OK] curl
)

php -m 2>nul | findstr /i "openssl" >nul
if %ERRORLEVEL% NEQ 0 (
    echo   [X] openssl TIDAK AKTIF!
    set "EXT_ERROR=1"
) else (
    echo   [OK] openssl
)

php -m 2>nul | findstr /i "fileinfo" >nul
if %ERRORLEVEL% NEQ 0 (
    echo   [X] fileinfo TIDAK AKTIF!
    set "EXT_ERROR=1"
) else (
    echo   [OK] fileinfo
)

if "%EXT_ERROR%"=="1" (
    echo.
    echo   [!] Ada extension PHP yang belum aktif.
    echo.
    echo   CARA FIX:
    echo   1. Buka file php.ini di folder PHP Anda
    echo      ^(biasanya C:\php\php.ini^)
    echo   2. Cari baris ^;extension=pdo_sqlite dan hapus tanda ^;
    echo   3. Lakukan juga untuk extension lain yang belum aktif
    echo   4. Pastikan baris extension_dir = "ext" tidak di-comment
    echo   5. Simpan dan jalankan setup.bat lagi
    echo.
    pause
    exit /b 1
)

echo.

:: ============================================================
:: [3/6] Setup Backend (Laravel)
:: ============================================================
echo [3/6] Setup Backend (Laravel + SQLite)...
echo.

cd /d "%~dp0backend"

:: Copy .env jika belum ada
if not exist .env (
    copy .env.example .env >nul
    echo   [OK] .env dibuat dari .env.example
) else (
    echo   [OK] .env sudah ada
)

:: Buat folder database jika belum ada
if not exist database\NUL mkdir database

:: Buat file SQLite
if not exist database\database.sqlite (
    type nul > database\database.sqlite
    echo   [OK] database\database.sqlite dibuat
) else (
    echo   [OK] database\database.sqlite sudah ada
)

:: === COMPOSER INSTALL ===
echo.
echo   Mengunduh PHP dependencies (composer install)...
echo   Ini mungkin memakan waktu 1-3 menit...
echo.
call composer install --no-interaction 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo.
    echo   [X] COMPOSER INSTALL GAGAL!
    echo   Pastikan koneksi internet aktif dan coba lagi.
    echo.
    pause
    exit /b 1
)
echo   [OK] Composer dependencies installed!
echo.

:: Generate APP_KEY
findstr /C:"GENERATE_NEW_KEY_HERE" .env >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    call php artisan key:generate
    echo   [OK] APP_KEY generated
)

:: Jalankan migrasi
echo   Menjalankan migrasi database...
call php artisan migrate --force
if %ERRORLEVEL% NEQ 0 (
    echo   [X] Migrasi gagal! Coba hapus database dan buat ulang:
    echo       del database\database.sqlite
    echo       type nul ^> database\database.sqlite
    echo       php artisan migrate
    pause
    exit /b 1
)
echo   [OK] Migrasi selesai

:: Seed database
echo   Mengisi data awal...
call php artisan db:seed --force
echo   [OK] Seeder selesai
echo.

cd /d "%~dp0"

:: ============================================================
:: [4/6] Setup Frontend (Vue + Tailwind)
:: ============================================================
echo [4/6] Setup Frontend (npm install)...
echo.

cd /d "%~dp0frontend"

echo   Mengunduh Node.js dependencies (npm install)...
echo   Ini mungkin memakan waktu 1-3 menit...
echo.
call npm install 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo.
    echo   [X] NPM INSTALL GAGAL!
    echo   Pastikan koneksi internet aktif.
    echo   Coba jalankan manual: cd frontend ^&^& npm install
    echo.
    pause
    exit /b 1
)
echo.
echo   [OK] Frontend dependencies installed!
echo.

cd /d "%~dp0"

:: ============================================================
:: [5/6] Setup Bot (Go + WhatsMeow)
:: ============================================================
echo [5/6] Setup Bot (go mod tidy)...
echo.

cd /d "%~dp0bot"

:: Copy .env jika belum ada
if not exist .env (
    copy .env.example .env >nul
    echo   [OK] .env bot dibuat
)

:: === GO GET + GO MOD TIDY ===
echo   Mengunduh Go modules...
echo   Ini mungkin memakan waktu 1-5 menit (pertama kali)...
echo.
echo   [1/4] go get go.mau.fi/whatsmeow@latest
call go get go.mau.fi/whatsmeow@latest 2>&1
echo   [2/4] go get github.com/mattn/go-sqlite3@latest
call go get github.com/mattn/go-sqlite3@latest 2>&1
echo   [3/4] go get github.com/mdp/qrterminal/v3@latest
call go get github.com/mdp/qrterminal/v3@latest 2>&1
echo   [4/4] go get google.golang.org/protobuf@latest
call go get google.golang.org/protobuf@latest 2>&1
echo.
echo   Running go mod tidy...
call go mod tidy 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo.
    echo   [X] GO MOD TIDY GAGAL!
    echo   Pastikan koneksi internet aktif.
    echo   Coba jalankan manual:
    echo     cd bot
    echo     go get go.mau.fi/whatsmeow@latest
    echo     go get github.com/mattn/go-sqlite3@latest
    echo     go get github.com/mdp/qrterminal/v3@latest
    echo     go get google.golang.org/protobuf@latest
    echo     go mod tidy
    echo.
    pause
    exit /b 1
)
echo.
echo   [OK] Go modules downloaded!
echo.

cd /d "%~dp0"

:: ============================================================
:: [6/6] Selesai!
:: ============================================================
echo ============================================
echo.
echo   SETUP BERHASIL! ==================
echo.
echo ============================================
echo.
echo   Cara menjalankan:
echo.
echo     start-all.bat     (jalankan semua sekaligus)
echo.
echo   Atau satu-satu (buka 3 Command Prompt):
echo.
echo     start-backend.bat   (Terminal 1)
echo     start-frontend.bat  (Terminal 2)
echo     start-bot.bat       (Terminal 3)
echo.
echo   Akses Dashboard: http://localhost:3000
echo.
echo   Login:
echo     Email    : admin@mpp-bengkayang.go.id
echo     Password : password123
echo.
echo ============================================
echo.
pause
