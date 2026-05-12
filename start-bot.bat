@echo off
chcp 65001 >nul 2>&1
title MPP Chatbot - WhatsApp Bot (Go)

echo ============================================
echo   MPP Chatbot - WhatsApp Bot (WhatsMeow)
echo   Webhook: http://localhost:8080
echo ============================================
echo.
echo PENTING: Pastikan Backend sudah running terlebih dahulu!
echo.

cd /d "%~dp0bot"

:: Cek apakah .env ada
if not exist .env (
    echo [INFO] Membuat .env dari .env.example...
    copy .env.example .env >nul
)

:: Cek apakah go.sum ada (dependencies sudah didownload)
if not exist go.sum (
    echo [!] Go modules belum didownload!
    echo     Menjalankan go mod tidy...
    echo     Pastikan koneksi internet aktif.
    echo.
    call go mod tidy
    if %ERRORLEVEL% NEQ 0 (
        echo.
        echo [ERROR] go mod tidy gagal!
        echo Pastikan:
        echo   1. Go sudah terinstall (go version)
        echo   2. Koneksi internet aktif
        echo   3. Jalankan setup.bat terlebih dahulu
        echo.
        pause
        exit /b 1
    )
    echo.
    echo [OK] Go modules berhasil didownload!
    echo.
)

echo Pertama kali: QR code akan muncul di terminal.
echo Scan dengan WhatsApp ^> Settings ^> Linked Devices ^> Link a Device
echo.
echo Tekan Ctrl+C untuk menghentikan...
echo.

:: Jalankan Go bot
go run .

pause
