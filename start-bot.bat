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
echo Pertama kali: QR code akan muncul di terminal.
echo Scan dengan WhatsApp (Settings ^> Linked Devices ^> Link a Device)
echo.
echo Tekan Ctrl+C untuk menghentikan...
echo.

cd /d "%~dp0bot"

:: Cek apakah .env ada
if not exist .env (
    echo [INFO] Membuat .env dari .env.example...
    copy .env.example .env >nul
)

:: Jalankan Go bot
go run .

pause
