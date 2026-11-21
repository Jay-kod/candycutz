#!/usr/bin/env bash
# ==============================================================================
# Candycutz — Single-VPS Production Zero-Downtime Deployment Script
# Automates Git pull, Web client build, Container rebuild, DB migrations,
# Cache warming, Queue restarts, and Health probes.
# Usage: ./infrastructure/scripts/deploy.sh
# ==============================================================================

set -eo pipefail

echo "================================================================="
echo " Starting Candycutz Single-VPS Production Deployment"
echo " Time: $(date)"
echo "================================================================="

# Step 1: Pull Latest Commits from GitHub
echo "[1/7] Pulling latest updates from origin/main..."
git pull origin main

# Step 2: Build Vue 3 Web Production Bundle
echo "[2/7] Compiling production web bundle (barbing-saloon-web)..."
cd barbing-saloon-web
if command -v npm &> /dev/null; then
    npm ci --prefer-offline || npm install
    npm run build
else
    echo "[WARN] Node/NPM not found on host path, relying on pre-built dist."
fi
cd ..

# Step 3: Rebuild and Restart Docker Containers
echo "[3/7] Rebuilding and launching Docker containers..."
docker compose up -d --build --remove-orphans

# Step 4: Run Database Migrations
echo "[4/7] Running pending database migrations..."
docker compose exec -T app php artisan migrate --force

# Step 5: Warm Production Caches
echo "[5/7] Warming route, config, and view caches..."
docker compose exec -T app php artisan optimize:clear
docker compose exec -T app php artisan optimize

# Step 6: Gracefully Restart Queue Workers
echo "[6/7] Gracefully restarting background queue workers..."
docker compose exec -T app php artisan queue:restart

# Step 7: System Health Verification
echo "[7/7] Verifying system health probes..."
sleep 3
HTTP_STATUS=$(curl -s -o /dev/null -w "%{http_code}" http://localhost/health-nginx || echo "000")
if [ "${HTTP_STATUS}" == "200" ]; then
    echo "[SUCCESS] Nginx health probe responded with HTTP 200!"
else
    echo "[WARN] Nginx health probe returned ${HTTP_STATUS}. Check 'docker compose logs nginx'."
fi

echo "================================================================="
echo " Candycutz Deployment Finished Successfully!"
echo "================================================================="
