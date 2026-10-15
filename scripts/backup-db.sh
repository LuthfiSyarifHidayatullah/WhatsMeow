#!/usr/bin/env bash
# =====================================================
# Backup database MySQL (Siap Bengkayang) dari container Docker.
# Menyimpan file .sql.gz ber-timestamp dan menghapus backup > 14 hari.
#
# Pakai manual:   ./scripts/backup-db.sh
# Pakai via cron: lihat DEPLOY.md (dijadwalkan harian).
# =====================================================
set -euo pipefail

# Jalankan dari direktori project (lokasi file .env & compose).
cd "$(dirname "$0")/.."

# Muat variabel dari .env (DB_DATABASE, DB_ROOT_PASSWORD, dll).
if [ -f .env ]; then
  set -a
  # shellcheck disable=SC1091
  . ./.env
  set +a
else
  echo "ERROR: file .env tidak ditemukan di $(pwd)" >&2
  exit 1
fi

BACKUP_DIR="./backups"
RETENTION_DAYS=14
mkdir -p "$BACKUP_DIR"

TIMESTAMP="$(date +%Y%m%d_%H%M%S)"
OUTFILE="$BACKUP_DIR/${DB_DATABASE}_${TIMESTAMP}.sql.gz"

echo "[backup] Membuat dump database '${DB_DATABASE}' ..."
docker compose -f docker-compose.prod.yml exec -T mysql \
  mysqldump -u root -p"${DB_ROOT_PASSWORD}" \
  --single-transaction --quick --routines --triggers \
  "${DB_DATABASE}" | gzip > "$OUTFILE"

echo "[backup] Tersimpan: $OUTFILE"

# Hapus backup lama.
find "$BACKUP_DIR" -name "${DB_DATABASE}_*.sql.gz" -type f -mtime +${RETENTION_DAYS} -delete
echo "[backup] Backup lebih dari ${RETENTION_DAYS} hari dibersihkan."
