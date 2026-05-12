@echo off
chcp 65001 >nul 2>&1
title MPP Bot - Setup Go Dependencies

echo ============================================
echo   MPP Bot - Download Go Dependencies
echo ============================================
echo.
echo   Pastikan koneksi internet aktif!
echo   Ini akan memakan waktu 1-5 menit...
echo.

cd /d "%~dp0"

:: Install dependencies satu per satu
echo [1/4] Downloading go.mau.fi/whatsmeow...
call go get go.mau.fi/whatsmeow@latest
if %ERRORLEVEL% NEQ 0 (
    echo [ERROR] Gagal download whatsmeow
    pause
    exit /b 1
)

echo [2/4] Downloading github.com/mattn/go-sqlite3...
call go get github.com/mattn/go-sqlite3@latest

echo [3/4] Downloading github.com/mdp/qrterminal/v3...
call go get github.com/mdp/qrterminal/v3@latest

echo [4/4] Downloading google.golang.org/protobuf...
call go get google.golang.org/protobuf@latest

echo.
echo Running go mod tidy...
call go mod tidy
if %ERRORLEVEL% NEQ 0 (
    echo [ERROR] go mod tidy gagal!
    pause
    exit /b 1
)

echo.
echo ============================================
echo   [OK] Semua Go dependencies berhasil didownload!
echo ============================================
echo.
echo   Jalankan bot dengan: go run .
echo.
pause
