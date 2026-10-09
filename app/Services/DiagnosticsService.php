<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Throwable;
use ZipArchive;

class DiagnosticsService
{
    /**
     * Absolute path to temporary storage directory
     */
    protected string $targetDir;

    public function __construct()
    {
        $this->targetDir = base_path('backups');
    }

    /**
     * Generate diagnostic system snapshot package (DB + Code).
     *
     * @param bool $includeEnv
     * @return array
     * @throws Exception
     */
    public function generateSnapshot(bool $includeEnv = false): array
    {
        @set_time_limit(600);
        @ini_set('memory_limit', '512M');

        $this->ensureTargetDirExists();
        $timestamp = date('Y-m-d_H-i-s');

        $sqlFilename = "database_{$timestamp}.sql";
        $sqlPath = $this->targetDir . '/' . $sqlFilename;

        $zipFilename = "project_backup_{$timestamp}.zip";
        $zipPath = $this->targetDir . '/' . $zipFilename;

        try {
            // 1. Generate Database SQL Dump
            $dbSuccess = $this->createDatabaseDump($sqlPath);
            if (!$dbSuccess || !file_exists($sqlPath) || filesize($sqlPath) === 0) {
                throw new Exception('Database export failed or generated empty file.');
            }

            // 2. Generate System Source Code Archive
            $zipSuccess = $this->createSourceZip($zipPath, $sqlPath, $sqlFilename, $includeEnv);
            if (!$zipSuccess || !file_exists($zipPath) || filesize($zipPath) === 0) {
                throw new Exception('Archive export failed or generated empty file.');
            }

            // Cleanup SQL file after adding it to ZIP
            if (file_exists($sqlPath)) {
                @unlink($sqlPath);
            }

            // Clean old temporary files
            $this->cleanOldFiles(3600);

            return [
                'file_path' => $zipPath,
                'file_name' => $zipFilename,
                'mime_type' => 'application/zip',
            ];
        } catch (Throwable $e) {
            if (file_exists($sqlPath)) {
                @unlink($sqlPath);
            }
            if (file_exists($zipPath)) {
                @unlink($zipPath);
            }
            Log::error('System Diagnostic Exception: ' . $e->getMessage(), [
                'exception' => $e,
                'trace'     => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    protected function ensureTargetDirExists(): void
    {
        if (!file_exists($this->targetDir)) {
            mkdir($this->targetDir, 0755, true);
        }
    }

    public function isMysqldumpAvailable(): bool
    {
        if (function_exists('exec')) {
            $output = [];
            $returnVar = 1;
            @exec('which mysqldump 2>&1', $output, $returnVar);
            if ($returnVar === 0 && !empty($output)) {
                return true;
            }
        }

        $commonPaths = [
            '/usr/bin/mysqldump',
            '/usr/local/bin/mysqldump',
            '/usr/mysql/bin/mysqldump',
            '/usr/local/mysql/bin/mysqldump',
        ];

        foreach ($commonPaths as $path) {
            if (file_exists($path) && is_executable($path)) {
                return true;
            }
        }

        return false;
    }

    protected function createDatabaseDump(string $outputPath): bool
    {
        $connectionName = config('database.default');
        $config = config("database.connections.{$connectionName}");

        if (!$config) {
            throw new Exception("Database configuration for [{$connectionName}] not found.");
        }

        $host = $config['host'] ?? '127.0.0.1';
        $port = (string) ($config['port'] ?? '3306');
        $database = $config['database'] ?? '';
        $username = $config['username'] ?? '';
        $password = (string) ($config['password'] ?? '');
        $socket = $config['socket'] ?? '';

        if (empty($database)) {
            throw new Exception("Configured database name is empty.");
        }

        if ($this->isMysqldumpAvailable()) {
            $dumpSuccess = $this->dumpViaMysqldump($host, $port, $username, $password, $database, $socket, $outputPath);
            if ($dumpSuccess && file_exists($outputPath) && filesize($outputPath) > 0) {
                return true;
            }
            Log::warning('Native mysqldump unavailable/failed. Running PHP PDO fallback.');
        }

        return $this->dumpViaPhpPdo($outputPath);
    }

    protected function dumpViaMysqldump(
        string $host,
        string $port,
        string $username,
        string $password,
        string $database,
        string $socket,
        string $outputPath
    ): bool {
        $cmdParts = ['mysqldump'];
        $cmdParts[] = '--single-transaction';
        $cmdParts[] = '--quick';
        $cmdParts[] = '--skip-lock-tables';
        $cmdParts[] = '--routines';
        $cmdParts[] = '--triggers';

        if (!empty($socket)) {
            $cmdParts[] = '--socket=' . escapeshellarg($socket);
        } else {
            if (!empty($host)) {
                $cmdParts[] = '--host=' . escapeshellarg($host);
            }
            if (!empty($port)) {
                $cmdParts[] = '--port=' . escapeshellarg($port);
            }
        }

        $cmdParts[] = '--user=' . escapeshellarg($username);
        $cmdParts[] = escapeshellarg($database);
        $cmdParts[] = '>' . escapeshellarg($outputPath);

        $command = implode(' ', $cmdParts);

        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $env = array_merge(getenv(), ['MYSQL_PWD' => $password]);
        $process = proc_open($command, $descriptors, $pipes, base_path(), $env);

        if (is_resource($process)) {
            fclose($pipes[0]);
            fclose($pipes[1]);
            $stderr = stream_get_contents($pipes[2]);
            fclose($pipes[2]);

            $exitCode = proc_close($process);

            if ($exitCode === 0 && file_exists($outputPath) && filesize($outputPath) > 0) {
                return true;
            }

            Log::error('Native mysqldump process failed', [
                'exit_code' => $exitCode,
                'stderr'    => $stderr,
            ]);
        }

        return false;
    }

    protected function dumpViaPhpPdo(string $outputPath): bool
    {
        $pdo = DB::connection()->getPdo();
        $pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);

        $handle = fopen($outputPath, 'w');
        if (!$handle) {
            throw new Exception("Failed to open dump file for writing: {$outputPath}");
        }

        $connectionName = config('database.default');
        $dbName = config("database.connections.{$connectionName}.database");

        fwrite($handle, "-- System Database Export\n");
        fwrite($handle, "-- Date: " . date('Y-m-d H:i:s') . "\n");
        fwrite($handle, "-- Database: {$dbName}\n\n");
        fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n");
        fwrite($handle, "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n");
        fwrite($handle, "SET time_zone = \"+00:00\";\n\n");

        $tablesStmt = $pdo->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");
        $tables = [];
        while ($row = $tablesStmt->fetch(\PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }

        foreach ($tables as $table) {
            fwrite($handle, "-- Table structure for table `{$table}`\n\n");
            fwrite($handle, "DROP TABLE IF EXISTS `{$table}`;\n");

            $createStmt = $pdo->query("SHOW CREATE TABLE `{$table}`");
            $createRow = $createStmt->fetch(\PDO::FETCH_NUM);
            if (isset($createRow[1])) {
                fwrite($handle, $createRow[1] . ";\n\n");
            }

            fwrite($handle, "-- Dumping data for table `{$table}`\n\n");

            $selectStmt = $pdo->query("SELECT * FROM `{$table}`");
            $columnCount = $selectStmt->columnCount();

            $batchSize = 200;
            $batchValues = [];

            while ($row = $selectStmt->fetch(\PDO::FETCH_NUM)) {
                $values = [];
                for ($i = 0; $i < $columnCount; $i++) {
                    if (is_null($row[$i])) {
                        $values[] = "NULL";
                    } elseif (is_numeric($row[$i])) {
                        $values[] = $row[$i];
                    } else {
                        $values[] = $pdo->quote($row[$i]);
                    }
                }
                $batchValues[] = "(" . implode(', ', $values) . ")";

                if (count($batchValues) >= $batchSize) {
                    fwrite($handle, "INSERT INTO `{$table}` VALUES\n" . implode(",\n", $batchValues) . ";\n");
                    $batchValues = [];
                }
            }

            if (count($batchValues) > 0) {
                fwrite($handle, "INSERT INTO `{$table}` VALUES\n" . implode(",\n", $batchValues) . ";\n");
            }

            fwrite($handle, "\n");
        }

        fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
        fclose($handle);

        return file_exists($outputPath) && filesize($outputPath) > 0;
    }

    protected function createSourceZip(string $zipPath, string $sqlPath, string $sqlFilename, bool $includeEnv): bool
    {
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return false;
        }

        if (file_exists($sqlPath)) {
            $zip->addFile($sqlPath, "database/{$sqlFilename}");
        }

        $basePath = realpath(base_path());

        $excludedDirs = [
            '.git',
            '.github',
            '.idea',
            '.vscode',
            'node_modules',
            'vendor',
            'scratch',
            'backups',
            'storage/framework/cache',
            'storage/framework/sessions',
            'storage/framework/views',
            'storage/logs',
            'storage/app/backups',
            'storage/app/temp_imports',
        ];

        $excludedFiles = [
            '.phpunit.result.cache',
            '$COOKIE_JAR',
            'cookies.txt',
            '.DS_Store',
        ];

        if (!$includeEnv) {
            $excludedFiles[] = '.env';
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($basePath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            $filePath = $file->getRealPath();
            $relativePath = str_replace($basePath . DIRECTORY_SEPARATOR, '', $filePath);
            $normalizedRelativePath = str_replace('\\', '/', $relativePath);

            $skipDir = false;
            foreach ($excludedDirs as $exDir) {
                if ($normalizedRelativePath === $exDir || str_starts_with($normalizedRelativePath, $exDir . '/')) {
                    $skipDir = true;
                    break;
                }
            }

            if ($skipDir) {
                continue;
            }

            $basename = $file->getBasename();
            if (in_array($basename, $excludedFiles, true)) {
                continue;
            }

            $ext = strtolower($file->getExtension());
            if (in_array($ext, ['zip', 'tar', 'gz', 'rar', '7z'], true)) {
                continue;
            }

            $zipInternalPath = 'project/' . $normalizedRelativePath;

            if ($file->isDir()) {
                $zip->addEmptyDir($zipInternalPath);
            } elseif ($file->isFile()) {
                $zip->addFile($filePath, $zipInternalPath);
            }
        }

        return $zip->close();
    }

    public function cleanOldFiles(int $maxAgeInSeconds = 3600): void
    {
        if (!file_exists($this->targetDir)) {
            return;
        }

        $files = glob($this->targetDir . '/*');
        $now = time();

        foreach ($files as $file) {
            if (is_file($file) && ($now - filemtime($file)) > $maxAgeInSeconds) {
                @unlink($file);
            }
        }
    }
}
