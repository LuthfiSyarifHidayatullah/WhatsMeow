@echo off
chcp 65001 >nul 2>&1
title MPP Chatbot - Frontend (Vue)

echo ============================================
echo   MPP Chatbot - Frontend (Vue + Tailwind)
echo   http://localhost:3000
echo ============================================
echo.

cd /d "%~dp0frontend"

:: Cek apakah node_modules ada
if not exist node_modules\NUL (
    echo [!] node_modules belum ada!
    echo     Menjalankan npm install...
    echo     Pastikan koneksi internet aktif.
    echo.
    call npm install
    if %ERRORLEVEL% NEQ 0 (
        echo.
        echo [ERROR] npm install gagal!
        echo Pastikan:
        echo   1. Node.js sudah terinstall (node -v)
        echo   2. Koneksi internet aktif
        echo   3. Jalankan setup.bat terlebih dahulu
        echo.
        pause
        exit /b 1
    )
    echo.
    echo [OK] Dependencies berhasil diinstall!
    echo.
)

echo Tekan Ctrl+C untuk menghentikan...
echo.

:: Jalankan Vite dev server
call npx vite --port 3000 --host

pause
