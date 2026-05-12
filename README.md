# MPP Chatbot - Mall Pelayanan Publik Kab. Bengkayang

Aplikasi WhatsApp Chatbot untuk Mall Pelayanan Publik (MPP) Pemerintah Kabupaten Bengkayang, dilengkapi dengan sistem monitoring real-time dan live chat petugas.

## Arsitektur Sistem

```
┌─────────────────┐     ┌──────────────────┐     ┌─────────────────┐
│   WhatsApp      │     │   Go Bot Service  │     │  Laravel Backend │
│   (Pengunjung)  │◄───►│   (WhatsMeow)     │◄───►│  (API + Logic)   │
└─────────────────┘     └──────────────────┘     └────────┬────────┘
                                                           │
                              ┌─────────────────────────────┤
                              │                             │
                    ┌─────────▼────────┐         ┌─────────▼────────┐
                    │   WebSocket       │         │   MySQL Database  │
                    │   (Real-time)     │         │                   │
                    └─────────┬────────┘         └───────────────────┘
                              │
                    ┌─────────▼────────┐
                    │   Vue Dashboard   │
                    │   (Monitoring &   │
                    │    Live Chat)     │
                    └──────────────────┘
```

## Fitur Utama

### 1. Chatbot WhatsApp (Otomatis)
- Menu layanan interaktif
- Respons otomatis berdasarkan keyword
- Pengenalan topik layanan (KTP, Pajak, Kepegawaian, dll)
- Eskalasi otomatis ke petugas bila diperlukan
- Rating kepuasan setelah selesai

### 2. Live Chat (Petugas)
- Menerima/menolak chat dari antrian
- Kirim pesan langsung ke WhatsApp pengunjung
- Transfer chat ke petugas/layanan lain
- Selesaikan percakapan

### 3. Monitoring Dashboard
- Statistik real-time (sesi aktif, antrian, selesai)
- Kinerja petugas per layanan
- Rata-rata waktu respons
- Rating kepuasan per layanan
- Alert antrian menunggu

### 4. Manajemen Admin
- CRUD Layanan (KTP, Pajak, Kepegawaian, dll)
- CRUD Petugas & penugasan layanan
- Kelola respons bot (keyword & template)
- Log aktivitas sistem

## Struktur Layanan MPP

| No | Layanan | Kode | Petugas |
|----|---------|------|---------|
| 1 | Pelayanan KTP & Kependudukan | ktp | Budi, Siti |
| 2 | Pelayanan Perpajakan | pajak | Ahmad, Dewi |
| 3 | Pelayanan Kepegawaian | pegawai | Eko |
| 4 | Pelayanan Perizinan | izin | Fitri |
| 5 | Pelayanan Kesehatan | kesehatan | Galih |
| 6 | Pelayanan Pendidikan | pendidikan | Hani |

## Tech Stack

| Komponen | Teknologi |
|----------|-----------|
| WhatsApp Bot | Go + WhatsMeow |
| Backend API | Laravel 11 (PHP 8.3) |
| Frontend Dashboard | Vue 3 + Tailwind CSS + Vite |
| Database | MySQL 8 |
| Real-time | Laravel WebSockets + Pusher Protocol |
| Auth | Laravel Sanctum (Token-based) |
| Containerization | Docker + Docker Compose |

## Alur Kerja Chatbot

```
Pengunjung kirim pesan WhatsApp
         │
         ▼
┌─────────────────────┐
│ Go Bot (WhatsMeow)  │──── Terima pesan
│                     │──── Kirim ke Laravel API
└─────────┬───────────┘
          │
          ▼
┌─────────────────────┐
│ Laravel API         │
│ ChatbotService      │
│                     │
│ 1. Cek sesi aktif   │
│ 2. Mode BOT:        │
│    - Match keyword   │
│    - Kirim respons   │
│ 3. Mode WAITING:    │
│    - Notify officer  │
│ 4. Mode ACTIVE:     │
│    - Forward ke      │
│      officer via WS  │
└─────────┬───────────┘
          │
          ▼
┌─────────────────────┐
│ Petugas (Dashboard) │──── Terima notifikasi
│                     │──── Balas via live chat
│                     │──── Pesan dikirim ke WA
└─────────────────────┘
```

## Setup & Installation

### Prerequisites
- Docker & Docker Compose
- Atau: PHP 8.3, Go 1.22+, Node.js 22+, MySQL 8

### Quick Start (Docker)

```bash
# Clone repository
git clone [repo-url]
cd mpp-chatbot

# Start semua services
docker-compose up -d

# Jalankan migration & seeder
docker exec mpp-backend php artisan migrate --seed

# Akses dashboard
# Frontend: http://localhost:3000
# Backend API: http://localhost:8000/api
```

### Manual Setup

#### 1. Database
```bash
mysql -u root -e "CREATE DATABASE mpp_chatbot;"
```

#### 2. Backend (Laravel)
```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

#### 3. Frontend (Vue)
```bash
cd frontend
npm install
npm run dev
```

#### 4. Bot (Go)
```bash
cd bot
cp .env.example .env
go mod tidy
go run .
# Scan QR code yang muncul di terminal
```

## API Endpoints

### Bot Webhook (dari Go service)
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| POST | `/api/bot/incoming` | Pesan masuk dari WhatsApp |
| POST | `/api/bot/message-status` | Update status pesan |

### Auth
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| POST | `/api/login` | Login petugas |
| POST | `/api/logout` | Logout |
| GET | `/api/me` | Get current user |

### Chat Sessions
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/api/chats` | Daftar sesi chat |
| GET | `/api/chats/{id}` | Detail sesi + pesan |
| POST | `/api/chats/{id}/accept` | Terima chat |
| POST | `/api/chats/{id}/transfer` | Transfer chat |
| POST | `/api/chats/{id}/resolve` | Selesaikan chat |
| POST | `/api/chats/{id}/messages` | Kirim pesan (officer) |

### Monitoring
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/api/monitoring/dashboard` | Statistik dashboard |
| GET | `/api/monitoring/services` | Statistik per layanan |
| GET | `/api/monitoring/officers` | Kinerja petugas |
| GET | `/api/monitoring/queue` | Status antrian |
| GET | `/api/monitoring/activity-logs` | Log aktivitas |

### CRUD Resources
| Resource | Endpoint |
|----------|----------|
| Layanan | `/api/services` |
| Petugas | `/api/users` |
| Respons Bot | `/api/bot-responses` |

## Akun Default

| Email | Password | Role |
|-------|----------|------|
| admin@mpp-bengkayang.go.id | password123 | Admin |
| supervisor@mpp-bengkayang.go.id | password123 | Supervisor |
| budi@mpp-bengkayang.go.id | password123 | Officer (KTP) |
| ahmad@mpp-bengkayang.go.id | password123 | Officer (Pajak) |
| eko@mpp-bengkayang.go.id | password123 | Officer (Kepegawaian) |

## WebSocket Events

| Event | Channel | Deskripsi |
|-------|---------|-----------|
| ChatEscalatedEvent | monitoring, officer.{id} | Chat di-eskalasi ke petugas |
| NewMessageEvent | chat-session.{id}, monitoring | Pesan baru masuk |

## Contoh Interaksi Bot

```
Pengunjung: halo
Bot: 🏛️ *Mall Pelayanan Publik*
     *Pemerintah Kabupaten Bengkayang*
     
     Selamat datang! Silakan pilih layanan:
     
     1. 📌 Pelayanan KTP & Kependudukan
     2. 📌 Pelayanan Perpajakan
     3. 📌 Pelayanan Kepegawaian
     4. 📌 Pelayanan Perizinan
     5. 📌 Pelayanan Kesehatan
     6. 📌 Pelayanan Pendidikan
     
     ---
     💬 Ketik *petugas* untuk bicara langsung
     ℹ️ Ketik nomor layanan untuk info lebih lanjut

Pengunjung: 1
Bot: 📋 *Pelayanan KTP & Kependudukan*
     Layanan pembuatan KTP, KK, Akta Kelahiran...
     
     Apakah Anda ingin:
     1. Lihat informasi lebih lanjut
     2. Hubungi petugas langsung

Pengunjung: syarat ktp
Bot: 📋 *Syarat Pembuatan KTP:*
     1. Fotocopy KK
     2. Surat Pengantar RT/RW
     ...

Pengunjung: petugas
Bot: ✅ Anda telah terhubung dengan petugas kami.
     👤 *Budi Santoso*
     📌 Pelayanan KTP & Kependudukan
```

## Lisensi

Hak Cipta © 2024 Pemerintah Kabupaten Bengkayang
