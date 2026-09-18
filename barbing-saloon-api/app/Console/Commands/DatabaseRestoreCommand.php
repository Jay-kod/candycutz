<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class DatabaseRestoreCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:restore 
                            {file? : Path to the backup snapshot file}
                            {--force : Bypass confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restore database from a backup snapshot file and verify post-restore integrity';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $connection = config('database.default');
        $filePath = $this->argument('file');

        if (! $filePath) {
            // Find most recent backup in default directory
            $dir = storage_path('app/backups');
            if (! is_dir($dir)) {
                $this->error("Backup directory does not exist: {$dir}");

                return self::FAILURE;
            }

            $files = glob($dir.DIRECTORY_SEPARATOR.'candycutz_backup_*.*');
            if (empty($files)) {
                $this->error("No backup files found in: {$dir}");

                return self::FAILURE;
            }

            // Sort by modification time descending
            usort($files, fn ($a, $b) => filemtime($b) <=> filemtime($a));
            $filePath = $files[0];
        }

        if (! file_exists($filePath)) {
            $this->error("Backup file not found at: {$filePath}");

            return self::FAILURE;
        }

        $this->warn("About to restore database [{$connection}] from: {$filePath}");

        if (! $this->option('force') && ! $this->confirm('Are you sure you want to restore? Current database records will be overwritten.')) {
            $this->info('Restore aborted by user.');

            return self::SUCCESS;
        }

        try {
            if ($connection === 'sqlite') {
                $dbPath = config('database.connections.sqlite.database');

                if ($dbPath === ':memory:') {
                    $source = new \PDO("sqlite:{$filePath}");
                    $target = DB::connection('sqlite')->getPdo();
                    $this->importSqliteDatabase($source, $target);
                } else {
                    DB::purge('sqlite');
                    try {
                        copy($filePath, $dbPath);
                    } catch (Throwable) {
                        $source = new \PDO("sqlite:{$filePath}");
                        $target = DB::connection('sqlite')->getPdo();
                        $this->importSqliteDatabase($source, $target);
                    }
                    DB::reconnect('sqlite');
                }
            } elseif ($connection === 'mysql') {
                $host = config('database.connections.mysql.host');
                $port = config('database.connections.mysql.port');
                $database = config('database.connections.mysql.database');
                $username = config('database.connections.mysql.username');
                $password = config('database.connections.mysql.password');

                $restoreCmd = sprintf(
                    'mysql --host=%s --port=%s --user=%s %s %s < %s',
                    escapeshellarg((string) $host),
                    escapeshellarg((string) $port),
                    escapeshellarg((string) $username),
                    $password ? '--password='.escapeshellarg((string) $password) : '',
                    escapeshellarg((string) $database),
                    escapeshellarg($filePath)
                );

                $result = null;
                $output = [];
                exec($restoreCmd, $output, $result);

                if ($result !== 0) {
                    $sql = file_get_contents($filePath);
                    if ($sql) {
                        DB::unprepared($sql);
                    }
                }
            } else {
                $sql = file_get_contents($filePath);
                if ($sql) {
                    DB::unprepared($sql);
                }
            }

            // Post-restore integrity verification
            $this->info('Performing post-restore integrity check...');

            $pdo = DB::connection()->getPdo();
            $tableCount = 0;
            if ($connection === 'sqlite') {
                $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
                $tableCount = count($tables);
            } else {
                $tables = DB::select('SHOW TABLES');
                $tableCount = count($tables);
            }

            $userCount = DB::table('users')->count();

            $this->info('Database restore drill SUCCESSFUL!');
            $this->line("Tables verified: <comment>{$tableCount}</comment>");
            $this->line("Users verified: <comment>{$userCount}</comment>");
            $this->line('Status: <comment>Operational</comment>');

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error('Restore failed: '.$e->getMessage());

            return self::FAILURE;
        }
    }

    private function importSqliteDatabase(\PDO $source, \PDO $target): void
    {
        $target->exec('PRAGMA foreign_keys = OFF;');
        $inTx = $target->inTransaction();
        if (! $inTx) {
            $target->beginTransaction();
        }

        try {
            $tables = $source->query("SELECT name, sql FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'")->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($tables as $table) {
                $tableName = $table['name'];
                $tableSql = $table['sql'];

                $target->exec("DROP TABLE IF EXISTS \"{$tableName}\"");
                if (! empty($tableSql)) {
                    $target->exec($tableSql);
                }

                $rows = $source->query("SELECT * FROM \"{$tableName}\"")->fetchAll(\PDO::FETCH_ASSOC);
                if (! empty($rows)) {
                    $cols = array_keys($rows[0]);
                    $colList = implode(', ', array_map(fn ($c) => "\"{$c}\"", $cols));
                    $paramList = implode(', ', array_fill(0, count($cols), '?'));
                    $stmt = $target->prepare("INSERT INTO \"{$tableName}\" ({$colList}) VALUES ({$paramList})");
                    foreach ($rows as $row) {
                        $stmt->execute(array_values($row));
                    }
                }
            }

            $indices = $source->query("SELECT sql FROM sqlite_master WHERE type='index' AND sql IS NOT NULL AND name NOT LIKE 'sqlite_%'")->fetchAll(\PDO::FETCH_COLUMN);
            foreach ($indices as $indexSql) {
                if (! empty($indexSql)) {
                    try {
                        $target->exec($indexSql);
                    } catch (Throwable) {
                        // Ignore duplicate index errors if created with table schema
                    }
                }
            }

            if (! $inTx && $target->inTransaction()) {
                $target->commit();
            }
        } catch (Throwable $e) {
            if (! $inTx && $target->inTransaction()) {
                $target->rollBack();
            }
            throw $e;
        } finally {
            $target->exec('PRAGMA foreign_keys = ON;');
        }
    }
}
