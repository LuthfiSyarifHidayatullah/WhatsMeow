@echo off
chcp 65001 >nul 2>&1
title MPP Frontend - Vue (port 3000)

cd /d "%~dp0frontend"

if not exist node_modules\NUL (
    echo [!] node_modules belum ada, jalankan: npm install
    call npm install
)

echo.
echo   MPP Frontend running di http://localhost:3000
echo   Tekan Ctrl+C untuk stop
echo.

call npx vite --port 3000 --host
pause
