#!/usr/bin/env bash
# ==============================================================================
# Candycutz — Automated MySQL Snapshot & Compression Script
# Creates a compressed, transaction-safe snapshot of candycutz_db.
# Usage: ./infrastructure/scripts/backup_db.sh [backup_destination_dir]
# ==============================================================================

set -eo pipefail

BACKUP_DIR="${1:-/var/backups/candycutz}"
DATE=$(date +"%Y%m%d_%H%M%S")
FILENAME="candycutz_backup_${DATE}.sql.gz"
TARGET_FILE="${BACKUP_DIR}/${FILENAME}"

# Ensure backup directory exists
mkdir -p "${BACKUP_DIR}"

echo "[INFO] Starting database backup for Candycutz..."
echo "[INFO] Target: ${TARGET_FILE}"

# Load credentials from .env if present
if [ -f ".env" ]; then
    DB_USER=$(grep -E '^DB_USERNAME=' .env | cut -d '=' -f2- | tr -d '"' | tr -d "'")
    DB_PASS=$(grep -E '^DB_PASSWORD=' .env | cut -d '=' -f2- | tr -d '"' | tr -d "'")
    DB_NAME=$(grep -E '^DB_DATABASE=' .env | cut -d '=' -f2- | tr -d '"' | tr -d "'")
fi

DB_USER="${DB_USER:-candycutz}"
DB_NAME="${DB_NAME:-candycutz_db}"
DB_PASS="${DB_PASS:-secret}"

# Execute mysqldump via docker compose
docker compose exec -T db mysqldump \
    -u "${DB_USER}" \
    -p"${DB_PASS}" \
    --single-transaction \
    --quick \
    --routines \
    --triggers \
    "${DB_NAME}" | gzip > "${TARGET_FILE}"

# Verify backup size
BACKUP_SIZE=$(du -h "${TARGET_FILE}" | cut -f1)
echo "[SUCCESS] Backup completed successfully (${BACKUP_SIZE}): ${TARGET_FILE}"

# Retention Policy: Prune backups older than 30 days
echo "[INFO] Pruning snapshots older than 30 days in ${BACKUP_DIR}..."
find "${BACKUP_DIR}" -type f -name "candycutz_backup_*.sql.gz" -mtime +30 -exec rm -f {} \;
echo "[INFO] Retention maintenance finished."
