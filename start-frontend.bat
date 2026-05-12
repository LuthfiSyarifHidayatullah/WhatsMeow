@echo off
chcp 65001 >nul 2>&1
title MPP Chatbot - Frontend (Vue)

echo ============================================
echo   MPP Chatbot - Frontend (Vue + Tailwind)
echo   http://localhost:3000
echo ============================================
echo.
echo Tekan Ctrl+C untuk menghentikan...
echo.

cd /d "%~dp0frontend"

:: Cek apakah node_modules ada
if not exist node_modules (
    echo [INFO] node_modules belum ada, installing dependencies...
    call npm install
    echo.
)

:: Jalankan Vite dev server
call npx vite --port 3000 --host

pause
