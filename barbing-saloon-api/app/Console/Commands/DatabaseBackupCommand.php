<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use Throwable;

class DatabaseBackupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup 
                            {--filename= : Custom filename for the backup}
                            {--directory= : Custom storage directory}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a consistent database backup snapshot for disaster recovery';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $connection = config('database.default');
        $this->info("Initiating database backup for connection [{$connection}]...");

        $dir = $this->option('directory') ?: storage_path('app/backups');
        if (! is_dir($dir)) {
            if (! mkdir($dir, 0755, true) && ! is_dir($dir)) {
                $this->error("Backup directory could not be created: {$dir}");

                return self::FAILURE;
            }
        }

        $isWritable = is_writable($dir);
        if (! $isWritable) {
            $testFile = $dir.DIRECTORY_SEPARATOR.uniqid('test_', true).'.tmp';
            if (@file_put_contents($testFile, 'test') !== false) {
                @unlink($testFile);
                $isWritable = true;
            }
        }

        if (! $isWritable) {
            $this->error("Backup directory is not writable: {$dir}");

            return self::FAILURE;
        }

        $timestamp = date('Y-m-d_His');
        $ext = $connection === 'sqlite' ? 'sqlite' : 'sql';
        $filename = $this->option('filename') ?: "candycutz_backup_{$timestamp}.{$ext}";
        if (basename($filename) !== $filename || ! preg_match('/\.(sql|sqlite)$/i', $filename)) {
            $this->error('Backup filename must be a plain .sql or .sqlite filename.');

            return self::FAILURE;
        }

        $destination = $dir.DIRECTORY_SEPARATOR.$filename;

        try {
            if (file_exists($destination)) {
                $this->error("A backup already exists with this filename: {$filename}");

                return self::FAILURE;
            }

            if ($connection === 'sqlite') {
                $dbPath = config('database.connections.sqlite.database');
                if ($dbPath === ':memory:') {
                    // Memory database snapshot: export to file
                    $pdo = DB::connection('sqlite')->getPdo();
                    $fileDb = new \PDO("sqlite:{$destination}");
                    // Export schema and data
                    $this->exportSqliteDatabase($pdo, $fileDb);
                } else {
                    if (! file_exists($dbPath)) {
                        $this->error("SQLite database file not found at: {$dbPath}");

                        return self::FAILURE;
                    }
                    if (! copy($dbPath, $destination)) {
                        $this->error("SQLite database copy failed for: {$dbPath}");

                        return self::FAILURE;
                    }
                }
            } elseif ($connection === 'mysql') {
                $host = config('database.connections.mysql.host');
                $port = config('database.connections.mysql.port');
                $database = config('database.connections.mysql.database');
                $username = config('database.connections.mysql.username');
                $password = config('database.connections.mysql.password');

                $process = new Process([
                    'mariadb-dump',
                    '--host='.(string) $host,
                    '--port='.(string) $port,
                    '--user='.(string) $username,
                    '--single-transaction',
                    '--quick',
                    '--routines',
                    '--triggers',
                    '--events',
                    '--hex-blob',
                    '--default-character-set=utf8mb4',
                    '--result-file='.$destination,
                    (string) $database,
                ]);
                $process->setTimeout(null);
                $process->run(null, ['MYSQL_PWD' => (string) $password]);

                if (! $process->isSuccessful()) {
                    $error = trim($process->getErrorOutput() ?: $process->getOutput());
                    throw new \RuntimeException($error !== '' ? $error : 'mariadb-dump exited unsuccessfully.');
                }
            } else {
                $this->error("Database driver [{$connection}] is not supported for backups.");

                return self::FAILURE;
            }

            if (! file_exists($destination) || filesize($destination) === 0) {
                $this->error("Backup failed: target file {$destination} is empty or missing.");

                return self::FAILURE;
            }

            $sizeKb = round(filesize($destination) / 1024, 2);
            $this->info('Backup successfully generated!');
            $this->line("Path: <comment>{$destination}</comment>");
            $this->line("Size: <comment>{$sizeKb} KB</comment>");
            $this->line("Timestamp: <comment>{$timestamp}</comment>");

            return self::SUCCESS;
        } catch (Throwable $e) {
            if (file_exists($destination)) {
                unlink($destination);
            }

            $this->error('Backup encountered an error: '.$e->getMessage());

            return self::FAILURE;
        }
    }

    private function exportSqliteDatabase(\PDO $source, \PDO $target): void
    {
        $target->exec('PRAGMA foreign_keys = OFF;');
        $inTx = $target->inTransaction();
        if (! $inTx) {
            $target->beginTransaction();
        }

        try {
            $tables = $source->query("SELECT name, sql FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'")->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($tables as $table) {
                if (! empty($table['sql'])) {
                    $target->exec($table['sql']);
                }
                $rows = $source->query("SELECT * FROM \"{$table['name']}\"")->fetchAll(\PDO::FETCH_ASSOC);
                if (! empty($rows)) {
                    $cols = array_keys($rows[0]);
                    $colList = implode(', ', array_map(fn ($c) => "\"{$c}\"", $cols));
                    $paramList = implode(', ', array_fill(0, count($cols), '?'));
                    $stmt = $target->prepare("INSERT INTO \"{$table['name']}\" ({$colList}) VALUES ({$paramList})");
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
