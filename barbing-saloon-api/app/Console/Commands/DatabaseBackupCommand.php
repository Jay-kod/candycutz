<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
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
            mkdir($dir, 0755, true);
        }

        $timestamp = date('Y-m-d_His');
        $ext = $connection === 'sqlite' ? 'sqlite' : 'sql';
        $filename = $this->option('filename') ?: "candycutz_backup_{$timestamp}.{$ext}";
        $destination = $dir.DIRECTORY_SEPARATOR.$filename;

        try {
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
                    copy($dbPath, $destination);
                }
            } elseif ($connection === 'mysql') {
                $host = config('database.connections.mysql.host');
                $port = config('database.connections.mysql.port');
                $database = config('database.connections.mysql.database');
                $username = config('database.connections.mysql.username');
                $password = config('database.connections.mysql.password');

                $dumpCmd = sprintf(
                    'mysqldump --host=%s --port=%s --user=%s %s %s > %s',
                    escapeshellarg((string) $host),
                    escapeshellarg((string) $port),
                    escapeshellarg((string) $username),
                    $password ? '--password='.escapeshellarg((string) $password) : '',
                    escapeshellarg((string) $database),
                    escapeshellarg($destination)
                );

                $result = null;
                $output = [];
                exec($dumpCmd, $output, $result);

                if ($result !== 0 || ! file_exists($destination)) {
                    // Fallback to PDO table export if mysqldump binary is unavailable
                    $this->exportPdoDatabase($destination);
                }
            } else {
                $this->exportPdoDatabase($destination);
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

    private function exportPdoDatabase(string $destination): void
    {
        $pdo = DB::connection()->getPdo();
        $tables = DB::connection()->getSchemaBuilder()->getTableListing();

        $sql = "-- CandyCutz Database Snapshot\n-- Generated at: ".date('Y-m-d H:i:s')."\n\n";

        foreach ($tables as $table) {
            $rows = $pdo->query("SELECT * FROM `{$table}`")->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($rows as $row) {
                $cols = array_keys($row);
                $colList = implode('`, `', $cols);
                $values = array_map(fn ($v) => $v === null ? 'NULL' : $pdo->quote((string) $v), array_values($row));
                $valList = implode(', ', $values);
                $sql .= "INSERT INTO `{$table}` (`{$colList}`) VALUES ({$valList});\n";
            }
        }

        file_put_contents($destination, $sql);
    }
}
