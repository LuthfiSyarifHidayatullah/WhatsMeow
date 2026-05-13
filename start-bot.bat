@echo off
chcp 65001 >nul 2>&1
title MPP Bot - WhatsApp (port 8080)

cd /d "%~dp0bot"

if not exist .env (
    copy .env.example .env >nul
)

if not exist go.sum (
    echo [!] Go modules belum ada, downloading...
    call go get go.mau.fi/whatsmeow@latest
    call go get github.com/mattn/go-sqlite3@latest
    call go get github.com/mdp/qrterminal/v3@latest
    call go get google.golang.org/protobuf@latest
    call go mod tidy
)

echo.
echo   MPP Bot starting...
echo   Scan QR code dengan WhatsApp jika muncul
echo   Tekan Ctrl+C untuk stop
echo.

go run .
pause
