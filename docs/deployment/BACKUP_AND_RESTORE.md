# Candycutz — Database Backup & Disaster Recovery Runbook

## 1. Automated Daily Backup Strategy

To ensure zero financial or booking data loss, backups of `candycutz_db` are generated daily at `02:00 AM WAT` (West Africa Time) and retained on a 30-day rolling cycle.

---

## 2. Manual Backup Creation

Execute a consistent database snapshot using `mysqldump` directly from the Docker container:

```bash
#!/bin/bash
# scripts/backup_db.sh
BACKUP_DIR="/var/backups/candycutz"
DATE=$(date +"%Y%m%d_%H%M%S")
FILENAME="candycutz_backup_${DATE}.sql.gz"

mkdir -p "$BACKUP_DIR"

echo "Creating compressed database snapshot: $FILENAME..."

docker compose exec -T db mysqldump \
  -u candycutz \
  -p"YOUR_STRONG_DATABASE_PASSWORD" \
  --single-transaction \
  --quick \
  --routines \
  --triggers \
  candycutz_db | gzip > "${BACKUP_DIR}/${FILENAME}"

echo "Backup completed successfully at ${BACKUP_DIR}/${FILENAME}"
```

Make the script executable:
```bash
chmod +x /var/www/candycutz/infrastructure/scripts/backup_db.sh
```

---

## 3. Automated Cron Schedule

Add the backup script to the root crontab on the VPS host:
```bash
crontab -e
```
Add line:
```cron
0 2 * * * /var/www/candycutz/infrastructure/scripts/backup_db.sh >> /var/log/candycutz_backup.log 2>&1
# Prune backups older than 30 days
0 3 * * * find /var/backups/candycutz -type f -name "*.sql.gz" -mtime +30 -delete
```

---

## 4. Disaster Recovery & Restoration Procedure

In the event of accidental data corruption or hardware migration, restore the database snapshot using this verified sequence:

### Step 1: Put Application in Maintenance Mode
```bash
docker compose exec app php artisan down --message="System maintenance in progress. We will be back shortly."
```

### Step 2: Unzip and Stream Backup into Database
```bash
BACKUP_FILE="/var/backups/candycutz/candycutz_backup_20260909_020000.sql.gz"

echo "Restoring database from $BACKUP_FILE..."

gunzip < "$BACKUP_FILE" | docker compose exec -T db mysql \
  -u candycutz \
  -p"YOUR_STRONG_DATABASE_PASSWORD" \
  candycutz_db

echo "Database restoration completed."
```

### Step 3: Run Pending Migrations & Clear Cache
```bash
docker compose exec app php artisan migrate --force
docker compose exec app php artisan optimize:clear
docker compose exec app php artisan optimize
```

### Step 4: Bring Application Back Online
```bash
docker compose exec app php artisan up
```

### Step 5: Verify Integrity
```bash
# Verify record counts
docker compose exec db mysql -u candycutz -p"YOUR_STRONG_DATABASE_PASSWORD" -e "SELECT count(*) FROM candycutz_db.appointments; SELECT count(*) FROM candycutz_db.users;"
```
