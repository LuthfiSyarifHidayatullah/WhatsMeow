@echo off
chcp 65001 >nul 2>&1
setlocal EnableDelayedExpansion

:: ============================================================
:: MPP Chatbot Kab. Bengkayang - Setup Script untuk Windows
:: ============================================================
:: Prerequisites:
::   - PHP >= 8.2 (pastikan ada di PATH)
::   - Composer (pastikan ada di PATH)
::   - Node.js >= 18 + npm (pastikan ada di PATH)
::   - Go >= 1.22 (pastikan ada di PATH)
:: ============================================================

echo ============================================
echo   MPP Chatbot Kab. Bengkayang - Setup
echo   (Windows Edition)
echo ============================================
echo.

:: ============================================================
:: [1/6] Cek Prerequisites
:: ============================================================
echo [1/6] Memeriksa prerequisites...
echo.

where php >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo [ERROR] PHP tidak ditemukan di PATH!
    echo Download: https://windows.php.net/download ^(Thread Safe, VS16 x64^)
    echo Pastikan folder PHP ditambahkan ke System PATH
    goto :error_exit
)
for /f "tokens=*" %%i in ('php -v 2^>^&1') do (
    echo   [OK] %%i
    goto :php_done
)
:php_done

where composer >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo [ERROR] Composer tidak ditemukan di PATH!
    echo Download: https://getcomposer.org/Composer-Setup.exe
    goto :error_exit
)
for /f "tokens=*" %%i in ('composer --version 2^>^&1') do (
    echo   [OK] %%i
    goto :composer_done
)
:composer_done

where node >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo [ERROR] Node.js tidak ditemukan di PATH!
    echo Download: https://nodejs.org/ ^(LTS version^)
    goto :error_exit
)
for /f "tokens=*" %%i in ('node --version 2^>^&1') do echo   [OK] Node.js %%i

where npm >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo [ERROR] npm tidak ditemukan di PATH!
    goto :error_exit
)
for /f "tokens=*" %%i in ('npm --version 2^>^&1') do echo   [OK] npm %%i

where go >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo [ERROR] Go tidak ditemukan di PATH!
    echo Download: https://go.dev/dl/ ^(Windows installer .msi^)
    goto :error_exit
)
for /f "tokens=*" %%i in ('go version 2^>^&1') do echo   [OK] %%i

echo.
echo   Semua prerequisites terpenuhi!
echo.

:: ============================================================
:: [2/6] Setup Backend (Laravel)
:: ============================================================
echo [2/6] Setup Backend (Laravel)...
echo.

cd backend

:: Copy .env jika belum ada
if not exist .env (
    copy .env.example .env >nul
    echo   [OK] File .env dibuat dari .env.example
) else (
    echo   [OK] File .env sudah ada
)

:: Buat folder database jika belum ada
if not exist database mkdir database

:: Buat file SQLite database
if not exist database\database.sqlite (
    type nul > database\database.sqlite
    echo   [OK] Database SQLite dibuat (database\database.sqlite)
) else (
    echo   [OK] Database SQLite sudah ada
)

:: Install Composer dependencies
echo   Installing Composer dependencies...
call composer install --no-interaction --no-progress 2>nul
if %ERRORLEVEL% EQU 0 (
    echo   [OK] Composer dependencies installed
) else (
    echo   [WARNING] Composer install gagal. Coba manual: cd backend ^&^& composer install
)

:: Generate APP_KEY jika masih default
findstr /C:"GENERATE_NEW_KEY_HERE" .env >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    call php artisan key:generate --quiet 2>nul
    echo   [OK] APP_KEY generated
)

:: Run migrations
call php artisan migrate --force --quiet 2>nul
if %ERRORLEVEL% EQU 0 (
    echo   [OK] Migrasi database selesai
) else (
    echo   [WARNING] Migrasi gagal. Coba manual: php artisan migrate
)

:: Seed database
call php artisan db:seed --force --quiet 2>nul
if %ERRORLEVEL% EQU 0 (
    echo   [OK] Database seeder selesai
) else (
    echo   [WARNING] Seeder gagal. Coba manual: php artisan db:seed
)

cd ..
echo.

:: ============================================================
:: [3/6] Setup Frontend (Vue + Tailwind)
:: ============================================================
echo [3/6] Setup Frontend (Vue + Tailwind)...
echo.

cd frontend

echo   Installing npm dependencies...
call npm install --silent 2>nul
if %ERRORLEVEL% EQU 0 (
    echo   [OK] Frontend dependencies installed
) else (
    echo   [WARNING] npm install gagal. Coba manual: cd frontend ^&^& npm install
)

cd ..
echo.

:: ============================================================
:: [4/6] Setup Bot (Go + WhatsMeow)
:: ============================================================
echo [4/6] Setup Bot (Go + WhatsMeow)...
echo.

cd bot

:: Copy .env jika belum ada
if not exist .env (
    copy .env.example .env >nul
    echo   [OK] File .env bot dibuat
) else (
    echo   [OK] File .env bot sudah ada
)

:: Download Go dependencies
echo   Downloading Go modules...
call go mod tidy 2>nul
if %ERRORLEVEL% EQU 0 (
    echo   [OK] Go modules downloaded
) else (
    echo   [WARNING] go mod tidy gagal. Coba manual: cd bot ^&^& go mod tidy
)

cd ..
echo.

:: ============================================================
:: [5/6] Cek PHP Extensions
:: ============================================================
echo [5/6] Memeriksa PHP extensions...
echo.

php -m 2>nul | findstr /i "pdo_sqlite" >nul
if %ERRORLEVEL% EQU 0 (
    echo   [OK] pdo_sqlite aktif
) else (
    echo   [WARNING] pdo_sqlite TIDAK AKTIF!
    echo   Edit php.ini: hapus ; di depan extension=pdo_sqlite
)

php -m 2>nul | findstr /i "mbstring" >nul
if %ERRORLEVEL% EQU 0 (
    echo   [OK] mbstring aktif
) else (
    echo   [WARNING] mbstring TIDAK AKTIF!
    echo   Edit php.ini: hapus ; di depan extension=mbstring
)

php -m 2>nul | findstr /i "openssl" >nul
if %ERRORLEVEL% EQU 0 (
    echo   [OK] openssl aktif
) else (
    echo   [WARNING] openssl TIDAK AKTIF!
)

php -m 2>nul | findstr /i "curl" >nul
if %ERRORLEVEL% EQU 0 (
    echo   [OK] curl aktif
) else (
    echo   [WARNING] curl TIDAK AKTIF!
)

echo.

:: ============================================================
:: [6/6] Selesai
:: ============================================================
echo [6/6] Setup selesai!
echo.
echo ============================================
echo   SETUP BERHASIL!
echo ============================================
echo.
echo Cara menjalankan (buka 3 Command Prompt terpisah):
echo.
echo   Terminal 1 - Backend:
echo     start-backend.bat
echo.
echo   Terminal 2 - Frontend:
echo     start-frontend.bat
echo.
echo   Terminal 3 - WhatsApp Bot:
echo     start-bot.bat
echo.
echo   Atau jalankan SEMUA sekaligus:
echo     start-all.bat
echo.
echo Akses:
echo   Dashboard : http://localhost:3000
echo   API       : http://localhost:8000/api
echo.
echo Login:
echo   Admin      : admin@mpp-bengkayang.go.id / password123
echo   Supervisor : supervisor@mpp-bengkayang.go.id / password123
echo   Petugas    : budi@mpp-bengkayang.go.id / password123
echo.
goto :end

:error_exit
echo.
echo ============================================
echo   SETUP GAGAL - Ada prerequisite yang belum terinstall
echo ============================================
echo.
echo Silakan install software yang dibutuhkan lalu jalankan setup.bat lagi.
echo.

:end
endlocal
pause
