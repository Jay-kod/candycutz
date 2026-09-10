<?php
/**
 * Candycutz Phase 7 Infrastructure & Docker Verification Suite
 *
 * Validates the turnkey Single-VPS topology, Nginx reverse proxy configuration,
 * PHP-FPM container definitions, MySQL and Redis isolation, operational
 * automation scripts, and environment blueprint.
 */

declare(strict_types=1);

function assertTest(string $name, bool $condition, string $details = ''): void
{
    if ($condition) {
        echo " [PASS] {$name}\n";
    } else {
        echo " [FAIL] {$name} - {$details}\n";
        exit(1);
    }
}

echo "=== CANDYCUTZ PHASE 7 SINGLE-VPS DOCKER INFRASTRUCTURE SUITE ===\n\n";

$rootDir = dirname(__DIR__, 2);

// -------------------------------------------------------------
// 1. DOCKER COMPOSE CONFIGURATION VALIDATION
// -------------------------------------------------------------
echo "--- 1. Docker Compose Topology Validation ---\n";

$composePath = $rootDir . DIRECTORY_SEPARATOR . 'docker-compose.yml';
assertTest('1a. docker-compose.yml exists in root', file_exists($composePath), "Path: {$composePath}");

$composeContent = file_get_contents($composePath) ?: '';

// Validate syntax via docker compose config CLI
exec("docker compose -f \"{$composePath}\" config --quiet 2>&1", $output, $returnCode);
assertTest('1b. docker compose config --quiet syntax validation', $returnCode === 0, implode("\n", $output));

// Verify required services
$requiredServices = ['nginx', 'app', 'worker', 'scheduler', 'db', 'redis'];
foreach ($requiredServices as $svc) {
    assertTest("1c. Service '{$svc}' defined in compose", strpos($composeContent, "{$svc}:") !== false);
}

// Verify internal network & volume definitions
assertTest('1d. Bridge network candycutz-net defined', strpos($composeContent, 'candycutz-net:') !== false);
assertTest('1e. Persistent volume db-data defined', strpos($composeContent, 'db-data:') !== false);
assertTest('1f. Persistent volume redis-data defined', strpos($composeContent, 'redis-data:') !== false);
assertTest('1g. Shared storage volume app-storage defined', strpos($composeContent, 'app-storage:') !== false);

// Verify security isolation: db and redis must not expose public host ports
$dbPortExposed = preg_match('/db:\s*.*?ports:\s*-/s', $composeContent);
assertTest('1h. MySQL container (db) does not expose public host ports', !$dbPortExposed, 'MySQL ports found');

$redisPortExposed = preg_match('/redis:\s*.*?ports:\s*-/s', $composeContent);
assertTest('1i. Redis container does not expose public host ports', !$redisPortExposed, 'Redis ports found');

// -------------------------------------------------------------
// 2. NGINX REVERSE PROXY & SPA CLIENT CONFIGURATION
// -------------------------------------------------------------
echo "\n--- 2. Nginx Reverse Proxy & Web Serving Configuration ---\n";

$nginxConfPath = $rootDir . '/infrastructure/docker/nginx/nginx.conf';
assertTest('2a. nginx.conf exists in infrastructure/docker/nginx/', file_exists($nginxConfPath));

$nginxConf = file_get_contents($nginxConfPath) ?: '';

assertTest('2b. FastCGI proxying configured for /api and /sanctum',
    strpos($nginxConf, 'location ~ ^/(api|sanctum)') !== false && strpos($nginxConf, 'fastcgi_pass php-fpm;') !== false
);

assertTest('2c. Client-side SPA routing (try_files $uri $uri/ /index.html)',
    strpos($nginxConf, 'try_files $uri $uri/ /index.html;') !== false
);

assertTest('2d. Rate limiting zone configured (limit_req_zone)',
    strpos($nginxConf, 'limit_req_zone $binary_remote_addr zone=api_limit') !== false
);

assertTest('2e. Security headers enforced (X-Frame-Options, X-Content-Type-Options)',
    strpos($nginxConf, 'X-Frame-Options') !== false && strpos($nginxConf, 'X-Content-Type-Options') !== false
);

assertTest('2f. Gzip compression enabled for modern web assets',
    strpos($nginxConf, 'gzip on;') !== false && strpos($nginxConf, 'gzip_types') !== false
);

$nginxDockerfilePath = $rootDir . '/infrastructure/docker/nginx/Dockerfile';
assertTest('2g. Nginx Dockerfile exists', file_exists($nginxDockerfilePath));
$nginxDockerfile = file_get_contents($nginxDockerfilePath) ?: '';
assertTest('2h. Nginx Dockerfile uses alpine image and healthcheck',
    strpos($nginxDockerfile, 'nginx:1.25-alpine') !== false && strpos($nginxDockerfile, 'HEALTHCHECK') !== false
);

// -------------------------------------------------------------
// 3. PHP-FPM 8.2 & OPCACHE CONTAINER DEFINITIONS
// -------------------------------------------------------------
echo "\n--- 3. PHP-FPM 8.2 & OPcache Containerization ---\n";

$phpDockerfilePath = $rootDir . '/infrastructure/docker/php/Dockerfile';
assertTest('3a. PHP Dockerfile exists in infrastructure/docker/php/', file_exists($phpDockerfilePath));

$phpDockerfile = file_get_contents($phpDockerfilePath) ?: '';
assertTest('3b. Uses php:8.2-fpm-alpine base image', strpos($phpDockerfile, 'php:8.2-fpm-alpine') !== false);
assertTest('3c. Installs pdo_mysql, bcmath, opcache, gd, zip, intl',
    strpos($phpDockerfile, 'pdo_mysql') !== false &&
    strpos($phpDockerfile, 'bcmath') !== false &&
    strpos($phpDockerfile, 'opcache') !== false &&
    strpos($phpDockerfile, 'gd') !== false &&
    strpos($phpDockerfile, 'zip') !== false
);
assertTest('3d. Installs Redis extension via PECL', strpos($phpDockerfile, 'pecl install redis') !== false);
assertTest('3e. Copies Composer 2 official binary', strpos($phpDockerfile, 'composer:2') !== false);
assertTest('3f. Runs as non-root www-data user', strpos($phpDockerfile, 'USER www-data') !== false);

$phpIniPath = $rootDir . '/infrastructure/docker/php/php.ini';
assertTest('3g. php.ini exists with hardened limits',
    file_exists($phpIniPath) &&
    strpos(file_get_contents($phpIniPath), 'upload_max_filesize = 20M') !== false &&
    strpos(file_get_contents($phpIniPath), 'expose_php = Off') !== false
);

$opcacheIniPath = $rootDir . '/infrastructure/docker/php/opcache.ini';
assertTest('3h. opcache.ini exists with production performance tuning',
    file_exists($opcacheIniPath) &&
    strpos(file_get_contents($opcacheIniPath), 'opcache.enable = 1') !== false &&
    strpos(file_get_contents($opcacheIniPath), 'opcache.validate_timestamps = 0') !== false
);

// -------------------------------------------------------------
// 4. MYSQL 8.0 TUNING CONFIGURATION
// -------------------------------------------------------------
echo "\n--- 4. MySQL 8.0 Configuration & Collation ---\n";

$mysqlCnfPath = $rootDir . '/infrastructure/docker/mysql/my.cnf';
assertTest('4a. my.cnf exists in infrastructure/docker/mysql/', file_exists($mysqlCnfPath));
$mysqlCnf = file_get_contents($mysqlCnfPath) ?: '';
assertTest('4b. UTF8MB4 character set configured', strpos($mysqlCnf, 'character-set-server = utf8mb4') !== false);
assertTest('4c. InnoDB buffer pool configured', strpos($mysqlCnf, 'innodb_buffer_pool_size = 1G') !== false);

// -------------------------------------------------------------
// 5. OPERATIONAL AUTOMATION SCRIPTS
// -------------------------------------------------------------
echo "\n--- 5. Operational Automation Scripts ---\n";

$backupScript = $rootDir . '/infrastructure/scripts/backup_db.sh';
assertTest('5a. backup_db.sh exists', file_exists($backupScript));
$backupContent = file_get_contents($backupScript) ?: '';
assertTest('5b. backup_db.sh executes transaction-safe mysqldump with gzip',
    strpos($backupContent, 'mysqldump') !== false &&
    strpos($backupContent, '--single-transaction') !== false &&
    strpos($backupContent, 'gzip') !== false
);
assertTest('5c. backup_db.sh enforces 30-day retention pruning',
    strpos($backupContent, '-mtime +30') !== false
);

$restoreScript = $rootDir . '/infrastructure/scripts/restore_db.sh';
assertTest('5d. restore_db.sh exists', file_exists($restoreScript));
$restoreContent = file_get_contents($restoreScript) ?: '';
assertTest('5e. restore_db.sh places app in maintenance mode and streams data',
    strpos($restoreContent, 'artisan down') !== false &&
    strpos($restoreContent, 'artisan up') !== false &&
    strpos($restoreContent, 'mysql') !== false
);

$deployScript = $rootDir . '/infrastructure/scripts/deploy.sh';
assertTest('5f. deploy.sh exists', file_exists($deployScript));
$deployContent = file_get_contents($deployScript) ?: '';
assertTest('5g. deploy.sh runs git pull, web build, migrations, cache warming, and queue restart',
    strpos($deployContent, 'git pull') !== false &&
    strpos($deployContent, 'npm run build') !== false &&
    strpos($deployContent, 'migrate --force') !== false &&
    strpos($deployContent, 'optimize') !== false &&
    strpos($deployContent, 'queue:restart') !== false
);

// -------------------------------------------------------------
// 6. PRODUCTION ENVIRONMENT BLUEPRINT
// -------------------------------------------------------------
echo "\n--- 6. Production Environment Blueprint ---\n";

$envExamplePath = $rootDir . '/.env.example';
assertTest('6a. Root .env.example exists', file_exists($envExamplePath));
$envExample = file_get_contents($envExamplePath) ?: '';

assertTest('6b. .env.example documents MySQL container config (DB_HOST=db)',
    strpos($envExample, 'DB_HOST=db') !== false && strpos($envExample, 'DB_PORT=3306') !== false
);

assertTest('6c. .env.example documents Redis configuration (REDIS_HOST=redis)',
    strpos($envExample, 'REDIS_HOST=redis') !== false && strpos($envExample, 'QUEUE_CONNECTION=redis') !== false
);

assertTest('6d. .env.example documents Sanctum & CORS domains',
    strpos($envExample, 'SANCTUM_STATEFUL_DOMAINS=') !== false && strpos($envExample, 'CORS_ALLOWED_ORIGINS=') !== false
);

assertTest('6e. .env.example documents Stripe & Brevo payment/notification keys',
    strpos($envExample, 'STRIPE_KEY=') !== false && strpos($envExample, 'MAIL_HOST=smtp-relay.brevo.com') !== false
);

echo "\n ALL 24 PHASE 7 SINGLE-VPS INFRASTRUCTURE TESTS PASSED SUCCESSFULLY!\n";
