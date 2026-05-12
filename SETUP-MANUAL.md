# Panduan Running Manual (Tanpa Docker)

Panduan ini menjelaskan cara menjalankan MPP Chatbot Kab. Bengkayang di komputer lokal **tanpa Docker**.

---

## Prerequisites

Pastikan software berikut sudah terinstall:

| Software | Versi Minimum | Cek Versi |
|----------|--------------|-----------|
| PHP | 8.2+ | `php -v` |
| Composer | 2.x | `composer -V` |
| Node.js | 18+ | `node -v` |
| npm | 9+ | `npm -v` |
| Go | 1.22+ | `go version` |

### PHP Extensions yang Dibutuhkan
```
pdo_sqlite, mbstring, xml, curl, zip, tokenizer, bcmath, json, openssl
```

Cek extensions aktif:
```bash
php -m | grep -E "pdo_sqlite|mbstring|xml|curl|zip"
```

### Install Prerequisites (Ubuntu/Debian)
```bash
# PHP & extensions
sudo apt install php8.3 php8.3-sqlite3 php8.3-mbstring php8.3-xml php8.3-curl php8.3-zip php8.3-bcmath

# Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Node.js (via nvm)
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.7/install.sh | bash
nvm install 22
nvm use 22

# Go
wget https://go.dev/dl/go1.22.4.linux-amd64.tar.gz
sudo tar -C /usr/local -xzf go1.22.4.linux-amd64.tar.gz
export PATH=$PATH:/usr/local/go/bin
```

### Install Prerequisites (Windows)
```
1. Download PHP: https://windows.php.net/download (Thread Safe)
2. Download Composer: https://getcomposer.org/Composer-Setup.exe
3. Download Node.js: https://nodejs.org/ (LTS)
4. Download Go: https://go.dev/dl/ (Windows installer)
```

### Install Prerequisites (macOS)
```bash
brew install php composer node go
```

---

## Quick Setup (Otomatis)

```bash
cd WhatsMeow
chmod +x setup.sh
./setup.sh
```

Script ini akan otomatis:
1. Memeriksa semua prerequisites
2. Setup Laravel backend dengan SQLite
3. Install dependencies frontend
4. Setup Go bot
5. Membuat start scripts

---

## Manual Setup (Step by Step)

### 1. Backend (Laravel)

```bash
cd backend

# Copy environment file
cp .env.example .env

# Edit .env untuk SQLite (TANPA MySQL!)
# Ubah baris berikut:
#   DB_CONNECTION=sqlite
#   (comment/hapus DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD)
#   BOT_API_TOKEN=mpp-bot-secret-token-2024

# Buat file database SQLite
touch database/database.sqlite

# Install dependencies
composer install

# Generate application key
php artisan key:generate

# Jalankan migrasi database
php artisan migrate

# Isi data awal (layanan + akun petugas)
php artisan db:seed

# Test: jalankan backend
php artisan serve
# Output: Starting Laravel development server: http://127.0.0.1:8000
```

#### File `.env` Backend (untuk SQLite):
```env
APP_NAME="MPP Chatbot Bengkayang"
APP_ENV=local
APP_KEY=base64:AKAN_DIGENERATE_OTOMATIS
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=mpp_chatbot
# DB_USERNAME=root
# DB_PASSWORD=

BROADCAST_DRIVER=pusher
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
SESSION_DRIVER=file

PUSHER_APP_ID=mpp-local
PUSHER_APP_KEY=mpp-local-key
PUSHER_APP_SECRET=mpp-local-secret
PUSHER_HOST=127.0.0.1
PUSHER_PORT=6001
PUSHER_SCHEME=http
PUSHER_APP_CLUSTER=mt1

BOT_API_TOKEN=mpp-bot-secret-token-2024
BOT_WEBHOOK_URL=http://localhost:8080
```

#### Opsional: Gunakan MySQL
Jika ingin pakai MySQL, buat database dulu:
```bash
mysql -u root -p -e "CREATE DATABASE mpp_chatbot CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```
Lalu set `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mpp_chatbot
DB_USERNAME=root
DB_PASSWORD=password_anda
```

---

### 2. Frontend (Vue + Tailwind)

```bash
cd frontend

# Install dependencies
npm install

# Jalankan dev server
npm run dev -- --port 3000 --host
# Output: Local: http://localhost:3000/
```

Frontend akan otomatis proxy request `/api` ke `http://localhost:8000` (sudah dikonfigurasi di `vite.config.js`).

---

### 3. WhatsApp Bot (Go)

```bash
cd bot

# Copy environment file
cp .env.example .env

# Download dependencies
go mod tidy

# Jalankan bot
go run .

# Pertama kali: akan muncul QR code di terminal
# Scan dengan WhatsApp (Settings > Linked Devices > Link a Device)
```

> **Catatan**: Setelah scan QR code pertama kali, session disimpan di file `whatsapp.db`. 
> Login berikutnya tidak perlu scan lagi.

---

## Menjalankan Semua Service

Buka **3 terminal** terpisah:

### Terminal 1 - Backend
```bash
cd WhatsMeow/backend
php artisan serve --host=0.0.0.0 --port=8000
```

### Terminal 2 - Frontend
```bash
cd WhatsMeow/frontend
npm run dev -- --port 3000 --host
```

### Terminal 3 - WhatsApp Bot
```bash
cd WhatsMeow/bot
go run .
```

Atau gunakan script all-in-one:
```bash
./start-all.sh
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

## Troubleshooting

### Backend: "Class not found" error
```bash
cd backend
composer dump-autoload
```

### Backend: Migration error dengan SQLite
```bash
# Hapus database dan buat ulang
rm database/database.sqlite
touch database/database.sqlite
php artisan migrate --seed
```

### Frontend: Port 3000 sudah dipakai
```bash
# Gunakan port lain
npm run dev -- --port 3001 --host
```

### Bot: QR code tidak muncul
```bash
# Pastikan terminal mendukung karakter Unicode
# Atau hapus session lama:
rm bot/whatsapp.db
go run .
```

### Bot: "connection refused" ke Laravel
Pastikan backend Laravel sudah running di port 8000 sebelum menjalankan bot.

### Frontend: API request gagal (CORS / 404)
Pastikan:
1. Backend running di `http://localhost:8000`
2. Frontend Vite proxy sudah benar di `vite.config.js`

### Reset semua data
```bash
cd backend
php artisan migrate:fresh --seed
```

---

## Struktur Port

```
┌─────────────────────────────────────────────┐
│  localhost:3000  →  Vue Dashboard (Frontend) │
│  localhost:8000  →  Laravel API (Backend)    │
│  localhost:8080  →  Go Bot Webhook           │
│  localhost:6001  →  WebSocket (opsional)     │
└─────────────────────────────────────────────┘
```

---

## Development Tips

### Hot Reload
- **Frontend**: Otomatis hot-reload saat edit file `.vue`
- **Backend**: Restart `php artisan serve` setelah ubah file PHP
- **Bot**: Restart `go run .` setelah ubah file Go

### Tanpa WebSocket (Development Sederhana)
Untuk development awal, WebSocket (real-time) bisa dilewati. Dashboard akan tetap berfungsi dengan polling (auto-refresh setiap 10-15 detik yang sudah ada di kode).

### Build Frontend untuk Production
```bash
cd frontend
npm run build
# Output di folder: dist/
# Serve dengan nginx atau serve statis lainnya
```
