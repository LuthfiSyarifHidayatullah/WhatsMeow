# Panduan Running di Windows (Tanpa Docker)

Panduan lengkap menjalankan **MPP Chatbot Kab. Bengkayang** di Windows tanpa Docker.

---

## Prerequisites (Wajib Install)

| Software | Versi Min. | Download | Cek Instalasi |
|----------|-----------|----------|---------------|
| PHP | 8.2+ | [windows.php.net/download](https://windows.php.net/download) (Thread Safe, VS16 x64) | `php -v` |
| Composer | 2.x | [getcomposer.org](https://getcomposer.org/Composer-Setup.exe) | `composer -V` |
| Node.js | 18+ | [nodejs.org](https://nodejs.org/) (LTS) | `node -v` |
| Go | 1.22+ | [go.dev/dl](https://go.dev/dl/) (Windows .msi installer) | `go version` |

---

## Install Prerequisites Step-by-Step

### 1. Install PHP

1. Download **PHP 8.3** (Thread Safe, VS16 x64 Zip) dari https://windows.php.net/download
2. Extract ke `C:\php`
3. Tambahkan `C:\php` ke System PATH:
   - Buka **Settings** > Cari "Environment Variables"
   - Edit **Path** di System Variables > **New** > ketik `C:\php`
4. Rename `C:\php\php.ini-development` menjadi `C:\php\php.ini`
5. Edit `C:\php\php.ini`, hapus `;` (titik-koma) di depan baris berikut:
   ```ini
   extension=curl
   extension=fileinfo
   extension=mbstring
   extension=openssl
   extension=pdo_sqlite
   extension=sqlite3
   extension=zip
   ```
6. Juga set `extension_dir`:
   ```ini
   extension_dir = "ext"
   ```
7. Test: buka **Command Prompt baru** dan ketik `php -v`

### 2. Install Composer

1. Download dan jalankan [Composer-Setup.exe](https://getcomposer.org/Composer-Setup.exe)
2. Ikuti wizard (akan otomatis mendeteksi PHP)
3. Test: buka **Command Prompt baru** dan ketik `composer -V`

### 3. Install Node.js

1. Download dan jalankan installer dari https://nodejs.org/ (pilih LTS)
2. Ikuti wizard instalasi (centang "Add to PATH")
3. Test: buka **Command Prompt baru** dan ketik `node -v` dan `npm -v`

### 4. Install Go

1. Download dan jalankan installer `.msi` dari https://go.dev/dl/
2. Ikuti wizard instalasi (otomatis ditambahkan ke PATH)
3. Test: buka **Command Prompt baru** dan ketik `go version`

> **PENTING**: Setelah install semua, tutup semua Command Prompt dan buka yang baru agar PATH ter-update.

---

## Quick Start (2 Langkah)

Buka **Command Prompt** (Win+R, ketik `cmd`, Enter), lalu:

```cmd
cd C:\path\ke\WhatsMeow

:: Langkah 1 - Setup (pertama kali saja)
setup.bat

:: Langkah 2 - Jalankan semua service
start-all.bat
```

Selesai! Dashboard terbuka di http://localhost:3000

---

## Setup Detail (Manual Step-by-Step)

Jika `setup.bat` bermasalah, ikuti langkah manual berikut:

### 1. Backend (Laravel)

Buka Command Prompt:

```cmd
cd WhatsMeow\backend

:: Copy file environment
copy .env.example .env

:: Buat file database SQLite
type nul > database\database.sqlite

:: Install PHP dependencies
composer install

:: Generate application key
php artisan key:generate

:: Jalankan migrasi database
php artisan migrate

:: Isi data awal (layanan + akun petugas)
php artisan db:seed

:: Test: jalankan server
php artisan serve
:: Buka http://localhost:8000/api/services - harus tampil JSON
```

### 2. Frontend (Vue + Tailwind)

Buka Command Prompt **baru**:

```cmd
cd WhatsMeow\frontend

:: Install dependencies
npm install

:: Jalankan development server
npx vite --port 3000 --host
:: Buka http://localhost:3000
```

### 3. WhatsApp Bot (Go)

Buka Command Prompt **baru**:

```cmd
cd WhatsMeow\bot

:: Copy file environment
copy .env.example .env

:: Download Go modules
go mod tidy

:: Jalankan bot
go run .
:: QR code akan muncul - scan dengan WhatsApp
```

---

## Menjalankan Aplikasi

### Opsi A: Start Semua Sekaligus (Recommended)

```cmd
start-all.bat
```

Ini akan membuka **3 window CMD terpisah**:
- Window "MPP Backend - Laravel" → port 8000
- Window "MPP Frontend - Vue" → port 3000
- Window "MPP Bot - WhatsApp" → port 8080

Untuk menghentikan: tutup masing-masing window.

### Opsi B: Start Satu-Satu

Buka 3 Command Prompt terpisah, masing-masing jalankan:

```cmd
:: Terminal 1
start-backend.bat

:: Terminal 2
start-frontend.bat

:: Terminal 3
start-bot.bat
```

### Opsi C: Manual

```cmd
:: Terminal 1
cd backend
php artisan serve --host=0.0.0.0 --port=8000

:: Terminal 2
cd frontend
npx vite --port 3000 --host

:: Terminal 3
cd bot
go run .
```

---

## Akses Aplikasi

| Komponen | URL |
|----------|-----|
| Dashboard Admin | http://localhost:3000 |
| Laravel API | http://localhost:8000/api |
| Bot Webhook | http://localhost:8080 |

---

## Akun Login Default

| Email | Password | Role | Layanan |
|-------|----------|------|---------|
| admin@mpp-bengkayang.go.id | password123 | Admin | - |
| supervisor@mpp-bengkayang.go.id | password123 | Supervisor | - |
| budi@mpp-bengkayang.go.id | password123 | Officer | KTP |
| siti@mpp-bengkayang.go.id | password123 | Officer | KTP |
| ahmad@mpp-bengkayang.go.id | password123 | Officer | Pajak |
| dewi@mpp-bengkayang.go.id | password123 | Officer | Pajak |
| eko@mpp-bengkayang.go.id | password123 | Officer | Kepegawaian |
| fitri@mpp-bengkayang.go.id | password123 | Officer | Perizinan |
| galih@mpp-bengkayang.go.id | password123 | Officer | Kesehatan |
| hani@mpp-bengkayang.go.id | password123 | Officer | Pendidikan |

---

## Daftar File Script Windows

| File | Fungsi |
|------|--------|
| `setup.bat` | Setup pertama kali (install deps, migrate, seed) |
| `start-all.bat` | Jalankan 3 service sekaligus (3 window baru) |
| `start-backend.bat` | Jalankan Laravel backend saja |
| `start-frontend.bat` | Jalankan Vue frontend saja |
| `start-bot.bat` | Jalankan WhatsApp bot saja |

---

## Troubleshooting Windows

### "php" is not recognized as an internal or external command

PHP belum ada di PATH. Solusi:
1. Pastikan PHP sudah di-extract ke `C:\php`
2. Tambahkan `C:\php` ke System PATH
3. **Tutup dan buka ulang** Command Prompt

### "composer" is not recognized

Composer belum terinstall atau PATH belum ter-update:
1. Jalankan ulang Composer-Setup.exe
2. Tutup dan buka ulang Command Prompt

### PHP Extension: "could not find driver" / SQLite error

Extension `pdo_sqlite` belum aktif:
1. Buka `C:\php\php.ini`
2. Cari baris `;extension=pdo_sqlite`
3. Hapus `;` di depannya menjadi `extension=pdo_sqlite`
4. Pastikan juga `extension_dir = "ext"` tidak di-comment
5. Restart Command Prompt

### npm: "ENOENT" atau "permission error"

Jalankan Command Prompt sebagai **Administrator**:
1. Klik kanan pada Command Prompt
2. Pilih "Run as administrator"
3. Coba lagi `npm install`

### Go: "cannot find module" saat `go mod tidy`

Pastikan koneksi internet aktif, Go perlu download dependencies:
```cmd
cd bot
set GOPROXY=https://proxy.golang.org,direct
go mod tidy
```

### Backend: "Class not found" error

```cmd
cd backend
composer dump-autoload
```

### Backend: Migration gagal

```cmd
cd backend
:: Hapus database lama dan buat ulang
del database\database.sqlite
type nul > database\database.sqlite
php artisan migrate --seed
```

### Port sudah dipakai (misalnya 8000 atau 3000)

Cek proses yang menggunakan port:
```cmd
netstat -ano | findstr :8000
:: Catat PID-nya, lalu:
taskkill /PID [nomor_PID] /F
```

Atau gunakan port lain:
```cmd
:: Backend di port 8001
php artisan serve --port=8001

:: Frontend di port 3001
npx vite --port 3001
```

### Bot: QR code tidak muncul atau karakter aneh

Terminal Windows mungkin tidak mendukung Unicode QR:
1. Gunakan **Windows Terminal** (download gratis dari Microsoft Store)
2. Atau gunakan **Git Bash**
3. Pastikan font terminal mendukung Unicode (Consolas / Cascadia Code)

### Bot: "connection refused" ke backend

Pastikan:
1. Backend sudah running (`start-backend.bat`)
2. Cek file `bot\.env`, pastikan `API_BASE_URL=http://localhost:8000/api`

### Frontend: halaman kosong / blank

1. Pastikan backend running
2. Buka Developer Tools browser (F12) > Console, lihat error
3. Pastikan `npm install` sudah berhasil
4. Coba clear cache: `npx vite --force`

---

## Reset Data (Mulai Ulang)

```cmd
cd backend
del database\database.sqlite
type nul > database\database.sqlite
php artisan migrate --seed
```

---

## Struktur Port

```
┌─────────────────────────────────────────────────┐
│  localhost:3000  →  Vue Dashboard (Frontend)     │
│  localhost:8000  →  Laravel API (Backend)        │
│  localhost:8080  →  Go Bot Webhook               │
└─────────────────────────────────────────────────┘

Frontend (port 3000) secara otomatis mem-proxy
request /api ke Backend (port 8000) via Vite config.
```

---

## Tips Development di Windows

### Gunakan Windows Terminal
Download **Windows Terminal** dari Microsoft Store untuk pengalaman yang lebih baik (tabs, split panes, Unicode support).

### Visual Studio Code
Recommended extensions:
- **Volar** (Vue Language Features)
- **Laravel Blade Snippets**
- **Go** (official Go extension)
- **Tailwind CSS IntelliSense**

### Hot Reload
- **Frontend**: Otomatis saat edit file `.vue` (Vite HMR)
- **Backend**: Restart `php artisan serve` setelah ubah PHP
- **Bot**: Restart `go run .` setelah ubah Go

### WebSocket (Opsional)
Untuk development awal, WebSocket tidak wajib. Dashboard sudah ada auto-refresh polling setiap 10-15 detik. WebSocket hanya diperlukan untuk notifikasi instant real-time.
