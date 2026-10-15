# Panduan Deploy Produksi (Ubuntu VPS + Docker)

Panduan menjalankan **Siap Bengkayang** di server produksi menggunakan Docker,
dengan HTTPS otomatis (Caddy) dan database MySQL.

Target: VPS Ubuntu, akses root, domain `chatbot.bengkayangkab.go.id`.

---

## Ringkasan Arsitektur

Semua berjalan sebagai container Docker:

```
Internet
   │  (HTTPS 443 / HTTP 80)
   ▼
[ caddy ]  reverse proxy + sertifikat TLS otomatis
   ▼
[ frontend ]  Nginx: menyajikan dashboard + proxy /api
   ▼
[ backend ]  Laravel API  ──►  [ mysql ]  database
   ▲                           ▲
[ bot ] WhatsApp (Go)          [ scheduler ] cron Laravel (timeout, presence)
```

Hanya **caddy** yang terbuka ke publik (port 80 & 443). Service lain hanya
berkomunikasi di jaringan internal Docker.

---

## Langkah 0 — Prasyarat

- [ ] **DNS**: arahkan `chatbot.bengkayangkab.go.id` ke IP VPS (record **A**).
      Verifikasi dari komputer Anda: `ping chatbot.bengkayangkab.go.id` →
      harus menunjuk IP VPS. (HTTPS gagal bila DNS belum benar.)
- [ ] **Firewall**: pastikan port **80** dan **443** terbuka ke publik.
- [ ] **Nomor WhatsApp** khusus bot sudah disiapkan (untuk scan QR).

---

## Langkah 1 — Install Docker di Server

Masuk ke server via SSH, lalu:

```bash
# Update & install Docker (script resmi)
curl -fsSL https://get.docker.com | sh

# Verifikasi
docker --version
docker compose version
```

---

## Langkah 2 — Ambil Kode

```bash
cd /opt
git clone https://github.com/LuthfiSyarifHidayatullah/WhatsMeow.git siap-bengkayang
cd siap-bengkayang
```

(Jika repo privat, siapkan kredensial git atau deploy key terlebih dahulu.)

---

## Langkah 3 — Konfigurasi (.env)

```bash
cp .env.prod.example .env
nano .env
```

Isi nilai berikut (lihat komentar di dalam file):

- `APP_DOMAIN` → `chatbot.bengkayangkab.go.id`
- `DB_ROOT_PASSWORD`, `DB_PASSWORD` → password kuat & acak
- `BOT_API_TOKEN` → token acak panjang. Buat cepat: `openssl rand -hex 32`
- `APP_KEY` → **biarkan kosong dulu**, akan diisi di Langkah 4.

Simpan (Ctrl+O, Enter) dan keluar (Ctrl+X).

---

## Langkah 4 — Generate APP_KEY

Build image backend sekali, lalu buat kunci aplikasi:

```bash
docker compose -f docker-compose.prod.yml build backend
docker compose -f docker-compose.prod.yml run --rm backend php artisan key:generate --show
```

Salin output (diawali `base64:...`), lalu tempel ke `APP_KEY=` di file `.env`:

```bash
nano .env   # isi APP_KEY=base64:....
```

---

## Langkah 5 — Jalankan Semua Service

```bash
docker compose -f docker-compose.prod.yml up -d --build
```

Cek status (semua harus `Up`; `mysql` bisa butuh ~30 detik jadi healthy):

```bash
docker compose -f docker-compose.prod.yml ps
```

---

## Langkah 6 — Migrasi Database

Buat tabel dan isi data awal (OPD, layanan, akun petugas, menu default):

```bash
docker compose -f docker-compose.prod.yml exec backend php artisan migrate --force
docker compose -f docker-compose.prod.yml exec backend php artisan db:seed --force
```

> Lihat **Lampiran A** bila Anda ingin memindahkan data dari komputer lokal
> (SQLite) alih-alih mulai dari seed.

---

## Langkah 7 — Tautkan Bot WhatsApp (Scan QR)

Bot perlu ditautkan ke nomor WhatsApp. Karena server tanpa layar, QR muncul di
log container:

```bash
docker compose -f docker-compose.prod.yml logs -f bot
```

Akan muncul **QR code** di terminal. Dari HP (nomor bot):
**WhatsApp → Setelan → Perangkat Tertaut → Tautkan Perangkat → scan QR**.

Setelah tertaut, akan muncul `Bot ... sudah aktif!`. Tekan Ctrl+C untuk berhenti
melihat log (bot tetap jalan di background). Sesi tersimpan di volume
`bot_data`, jadi tidak perlu scan ulang saat restart.

---

## Langkah 8 — Uji

- Buka `https://chatbot.bengkayangkab.go.id` → dashboard tampil, HTTPS aktif
  (ikon gembok). Login dengan akun admin dari seeder.
- Kirim pesan ke nomor WhatsApp bot → bot membalas menu.

---

## Backup Database Otomatis

Script backup sudah tersedia di `scripts/backup-db.sh` (dump harian, simpan 14 hari).

```bash
chmod +x scripts/backup-db.sh
./scripts/backup-db.sh          # uji manual; cek folder ./backups
```

Jadwalkan harian via cron (jam 02:00):

```bash
crontab -e
```
Tambahkan baris (sesuaikan path):
```
0 2 * * * cd /opt/siap-bengkayang && ./scripts/backup-db.sh >> ./backups/backup.log 2>&1
```

---

## Update Aplikasi (deploy versi baru)

Setiap ada perubahan kode (sudah di-merge ke `main`):

```bash
cd /opt/siap-bengkayang
git pull
docker compose -f docker-compose.prod.yml up -d --build
docker compose -f docker-compose.prod.yml exec backend php artisan migrate --force
```

Bot tidak perlu scan ulang (sesi di volume `bot_data`).

---

## Perintah Berguna

```bash
# Lihat log suatu service
docker compose -f docker-compose.prod.yml logs -f backend
docker compose -f docker-compose.prod.yml logs -f bot

# Restart satu service
docker compose -f docker-compose.prod.yml restart backend

# Hentikan semua
docker compose -f docker-compose.prod.yml down

# Masuk ke shell backend (mis. tinker)
docker compose -f docker-compose.prod.yml exec backend php artisan tinker
```

---

## Troubleshooting

**HTTPS gagal / sertifikat tidak terbit**
- Pastikan DNS `APP_DOMAIN` sudah menunjuk IP VPS (`ping` domainnya).
- Pastikan port 80 & 443 terbuka (Caddy butuh port 80 untuk verifikasi).
- Lihat log: `docker compose -f docker-compose.prod.yml logs caddy`.

**Dashboard tampil tapi API error / "Network Error"**
- Cek backend jalan: `docker compose -f docker-compose.prod.yml ps`.
- Lihat log backend: `... logs backend`.

**Bot tidak membalas**
- Pastikan bot tertaut (Langkah 7). Cek `... logs bot`.
- Jika muncul `Got 401: logged out`, bot ter-logout dari HP — scan ulang.

**Fitur timeout / status online petugas tidak berjalan**
- Pastikan service `scheduler` berstatus Up: `... ps`.

---

## Lampiran A — Migrasi Data dari Lokal (SQLite → MySQL)

> Hanya jika Anda ingin membawa data konfigurasi dari komputer lokal.
> Jika mulai bersih, lewati bagian ini (cukup Langkah 6).

Pendekatan paling aman untuk SQLite → MySQL adalah **mengekspor data terpilih**,
bukan menyalin file database. Rekomendasi: bawa data **konfigurasi** (OPD,
layanan, sub-menu, respons bot, akun petugas), dan biarkan data operasional
(sesi chat, pesan, feedback, log) mulai bersih.

Dua cara umum:

**Cara 1 — Re-seed + sesuaikan via dashboard (paling sederhana).**
Jalankan Langkah 6 (`migrate --seed`) untuk mendapatkan data dasar, lalu
tambahkan/ubah layanan & menu yang spesifik lewat dashboard (halaman
Layanan → Kelola Menu, dsb). Cocok bila kustomisasi lokal Anda sedikit.

**Cara 2 — Ekspor-impor terpilih.**
Perlu bantuan teknis untuk mengekspor tabel tertentu dari SQLite dan
meng-impor ke MySQL (urutan dependensi FK harus dijaga). Jika Anda memilih
cara ini, hubungi tim pengembang untuk script ekspor yang sesuai dengan
kondisi data Anda.
