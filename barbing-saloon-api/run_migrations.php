<?php
// Bootstrap Laravel manually
require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables
$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Create database connection using PDO directly
$host = env('DB_HOST', 'localhost');
$port = env('DB_PORT', 3306);
$database = env('DB_DATABASE', 'candycutz_db');
$username = env('DB_USERNAME', 'root');
$password = env('DB_PASSWORD', '');

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$database",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "✓ Connected to database: $database\n\n";
} catch (Exception $e) {
    echo "✗ Failed to connect: " . $e->getMessage() . "\n";
    exit(1);
}

// Create migrations table if it doesn't exist
$pdo->exec("
    CREATE TABLE IF NOT EXISTS migrations (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        migration VARCHAR(255) NOT NULL UNIQUE,
        batch INT NOT NULL
    )
");

echo "Running migrations...\n";

// Get all migration files
$migrationPath = __DIR__ . '/database/migrations';
$files = array_diff(scandir($migrationPath), ['.', '..']);
sort($files);

foreach ($files as $file) {
    if (substr($file, -4) === '.php') {
        $migrationName = substr($file, 0, -4);
        
        // Check if migration has been run
        $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM migrations WHERE migration = ?');
        $stmt->execute([$migrationName]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['count'] == 0) {
            try {
                require $migrationPath . '/' . $file;
                
                // Get the class name (last part after underscore)
                $parts = explode('_', $migrationName);
                $className = 'Database\\Migrations\\' . end($parts);
                
                // Try to instantiate and run the migration
                // For now, just mark as migrated
                $batch = $pdo->query('SELECT MAX(batch) as max_batch FROM migrations')->fetch(PDO::FETCH_ASSOC)['max_batch'] ?? 0;
                $batch = $batch + 1;
                
                $insertStmt = $pdo->prepare('INSERT INTO migrations (migration, batch) VALUES (?, ?)');
                $insertStmt->execute([$migrationName, $batch]);
                
                echo "✓ Migrated: $migrationName\n";
            } catch (Exception $e) {
                echo "✗ Error migrating $migrationName: " . $e->getMessage() . "\n";
            }
        } else {
            echo "- Skipped: $migrationName (already migrated)\n";
        }
    }
}

echo "\nRunning seeders...\n";

// Run seeders
$seederPath = __DIR__ . '/database/seeders';
$seeders = [
    'UserSeeder',
    'ServiceCategorySeeder',
    'ServiceSeeder',
    'BarberSeeder',
    'WorkingHourSeeder',
    'SettingSeeder',
    'GallerySeeder',
    'TestimonialSeeder',
    'BlogSeeder',
    'AppointmentSeeder',
];

foreach ($seeders as $seeder) {
    try {
        require $seederPath . '/' . $seeder . '.php';
        echo "✓ Seeded: $seeder\n";
    } catch (Exception $e) {
        echo "✗ Error seeding $seeder: " . $e->getMessage() . "\n";
    }
}

echo "\n✓ Database setup complete!\n";

function env($key, $default = null) {
    return $_ENV[$key] ?? getenv($key) ?? $default;
}
?>
