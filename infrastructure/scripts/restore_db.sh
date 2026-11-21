#!/usr/bin/env bash
# ==============================================================================
# Candycutz — Disaster Recovery & Snapshot Restoration Script
# Restores a compressed MySQL backup into candycutz_db safely.
# Usage: ./infrastructure/scripts/restore_db.sh /path/to/backup.sql.gz
# ==============================================================================

set -eo pipefail

BACKUP_FILE="$1"

if [ -z "${BACKUP_FILE}" ]; then
    echo "[ERROR] Missing backup file argument!"
    echo "Usage: $0 /path/to/candycutz_backup_YYYYMMDD_HHMMSS.sql.gz"
    exit 1
fi

if [ ! -f "${BACKUP_FILE}" ]; then
    echo "[ERROR] Backup file not found: ${BACKUP_FILE}"
    exit 1
fi

# Load credentials from .env if present
if [ -f ".env" ]; then
    DB_USER=$(grep -E '^DB_USERNAME=' .env | cut -d '=' -f2- | tr -d '"' | tr -d "'")
    DB_PASS=$(grep -E '^DB_PASSWORD=' .env | cut -d '=' -f2- | tr -d '"' | tr -d "'")
    DB_NAME=$(grep -E '^DB_DATABASE=' .env | cut -d '=' -f2- | tr -d '"' | tr -d "'")
fi

DB_USER="${DB_USER:-candycutz}"
DB_NAME="${DB_NAME:-candycutz_db}"
DB_PASS="${DB_PASS:-secret}"

echo "================================================================="
echo " WARNING: THIS WILL OVERWRITE DATA IN ${DB_NAME}!"
echo " Restoring from: ${BACKUP_FILE}"
echo "================================================================="

read -p "Are you sure you want to proceed? [y/N]: " CONFIRM
if [[ "${CONFIRM}" != "y" && "${CONFIRM}" != "Y" ]]; then
    echo "[CANCELLED] Restoration aborted by user."
    exit 0
fi

# Step 1: Place Application into Maintenance Mode
echo "[1/5] Putting application into maintenance mode..."
docker compose exec -T app php artisan down --message="Database restoration in progress. Back shortly." || true

# Step 2: Stream Backup Snapshot into Database
echo "[2/5] Streaming backup file into MySQL..."
if [[ "${BACKUP_FILE}" == *.gz ]]; then
    gunzip -c "${BACKUP_FILE}" | docker compose exec -T db mysql -u "${DB_USER}" -p"${DB_PASS}" "${DB_NAME}"
else
    docker compose exec -T db mysql -u "${DB_USER}" -p"${DB_PASS}" "${DB_NAME}" < "${BACKUP_FILE}"
fi

# Step 3: Run Pending Migrations
echo "[3/5] Checking and running any pending migrations..."
docker compose exec -T app php artisan migrate --force

# Step 4: Clear & Warm Application Caches
echo "[4/5] Clearing and warming production caches..."
docker compose exec -T app php artisan optimize:clear
docker compose exec -T app php artisan optimize

# Step 5: Bring Application Back Online
echo "[5/5] Bringing application back online..."
docker compose exec -T app php artisan up

echo "[SUCCESS] Disaster recovery restoration completed successfully!"
