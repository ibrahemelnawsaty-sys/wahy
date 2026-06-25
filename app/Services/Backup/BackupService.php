<?php

namespace App\Services\Backup;

use Illuminate\Support\Facades\DB;
use ZipArchive;

/**
 * خدمة النسخ الاحتياطي — مستخرجة من SuperAdminController (200+ سطر).
 *
 * تدعم:
 * - أنواع: full / database-only / files-only
 * - محركات: SQLite (نسخ مباشر) + MySQL (mysqldump مع PHP fallback)
 * - تنظيف الملفات المؤقتة عند نهاية الـ request
 *
 * استخدام:
 *   $service = app(BackupService::class);
 *   $zipFile = $service->create('full'); // يرجع المسار الكامل للـ zip
 */
class BackupService
{
    /** المسار الجذري لتخزين النسخ */
    private string $backupDir;

    public function __construct()
    {
        $this->backupDir = storage_path('app/Laravel');

        if (! file_exists($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
    }

    /**
     * إنشاء نسخة احتياطية.
     *
     * @param  string  $type  full | database-only | files-only
     * @return string المسار الكامل للملف المُنشأ
     *
     * @throws \RuntimeException عند فشل إنشاء الـ zip
     */
    public function create(string $type = 'full'): string
    {
        $timestamp = date('Y-m-d_H-i-s');
        $dbDriver = config('database.default');

        $zipFile = match ($type) {
            'database-only' => $this->backupDir . "/database-backup-{$timestamp}.zip",
            'files-only' => $this->backupDir . "/files-backup-{$timestamp}.zip",
            default => $this->backupDir . "/full-backup-{$timestamp}.zip",
        };

        $zip = new ZipArchive;
        if ($zip->open($zipFile, ZipArchive::CREATE) !== true) {
            throw new \RuntimeException("Failed to open ZIP for writing: {$zipFile}");
        }

        try {
            if (in_array($type, ['database-only', 'full'], true)) {
                $this->addDatabase($zip, $dbDriver, $timestamp, $type === 'full');
            }

            if (in_array($type, ['files-only', 'full'], true)) {
                $this->addFiles($zip);
            }

            $zip->close();
        } catch (\Throwable $e) {
            $zip->close();
            @unlink($zipFile);
            throw $e;
        }

        return $zipFile;
    }

    /**
     * حذف نسخة احتياطية بأمان (مع منع path traversal).
     */
    public function delete(string $filename): bool
    {
        $filename = basename($filename); // 🔴 amnع path traversal
        $fullPath = $this->backupDir . '/' . $filename;

        if (! file_exists($fullPath)) {
            return false;
        }

        if (is_dir($fullPath)) {
            return $this->deleteDirectory($fullPath);
        }

        return @unlink($fullPath);
    }

    /**
     * قائمة بكل النسخ المتاحة (مرتبة بحسب التاريخ تنازلياً).
     *
     * @return array<int, array{filename: string, size: int, created_at: \DateTimeImmutable, type: string}>
     */
    public function list(): array
    {
        if (! is_dir($this->backupDir)) {
            return [];
        }

        $files = scandir($this->backupDir);
        $backups = [];

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $fullPath = $this->backupDir . '/' . $file;
            if (! is_file($fullPath)) {
                continue;
            }

            $type = match (true) {
                str_starts_with($file, 'database-backup') => 'database',
                str_starts_with($file, 'files-backup') => 'files',
                str_starts_with($file, 'full-backup') => 'full',
                default => 'other',
            };

            $backups[] = [
                'filename' => $file,
                'size' => filesize($fullPath),
                'created_at' => new \DateTimeImmutable('@' . filemtime($fullPath)),
                'type' => $type,
            ];
        }

        usort($backups, fn ($a, $b) => $b['created_at'] <=> $a['created_at']);

        return $backups;
    }

    /**
     * إضافة قاعدة البيانات إلى الـ zip.
     */
    private function addDatabase(ZipArchive $zip, string $driver, string $timestamp, bool $useNestedPath): void
    {
        $prefix = $useNestedPath ? 'database/' : '';

        if ($driver === 'sqlite') {
            $dbPath = database_path('database.sqlite');
            if (file_exists($dbPath)) {
                $zip->addFile($dbPath, $prefix . 'database.sqlite');
            }

            return;
        }

        // MySQL
        $dumpFile = storage_path("app/temp-dump-{$timestamp}.sql");

        // محاولة mysqldump أولاً، fallback لـ PHP
        $success = $this->createMysqlDump($dumpFile);

        if (! $success) {
            $this->createPhpMysqlDump($dumpFile);
        }

        if (file_exists($dumpFile)) {
            $zip->addFile($dumpFile, $prefix . 'mysql-dump.sql');

            // حذف الملف المؤقت بعد إغلاق الـ request
            register_shutdown_function(function () use ($dumpFile) {
                if (file_exists($dumpFile)) {
                    @unlink($dumpFile);
                }
            });
        }
    }

    private function createMysqlDump(string $outputFile): bool
    {
        $host = config('database.connections.mysql.host');
        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');

        $command = sprintf(
            'mysqldump --host=%s --user=%s --password=%s %s > %s',
            escapeshellarg($host),
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($database),
            escapeshellarg($outputFile),
        );

        @exec($command, $output, $returnVar);

        return $returnVar === 0 && file_exists($outputFile);
    }

    /**
     * MySQL dump عبر PHP (fallback عند غياب mysqldump CLI).
     */
    private function createPhpMysqlDump(string $outputFile): void
    {
        $tables = DB::select('SHOW TABLES');

        $dump = "-- MySQL Backup\n";
        $dump .= '-- Date: ' . date('Y-m-d H:i:s') . "\n\n";
        $dump .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table) {
            $tableName = array_values((array) $table)[0];

            // Structure
            $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`")[0];
            $dump .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
            $dump .= $createTable->{'Create Table'} . ";\n\n";

            // Data
            $rows = DB::table($tableName)->get();
            if ($rows->count() > 0) {
                $dump .= "INSERT INTO `{$tableName}` VALUES\n";
                $values = [];
                foreach ($rows as $row) {
                    $rowData = array_map(
                        fn ($v) => is_null($v) ? 'NULL' : "'" . addslashes((string) $v) . "'",
                        (array) $row,
                    );
                    $values[] = '(' . implode(',', $rowData) . ')';
                }
                $dump .= implode(",\n", $values) . ";\n\n";
            }
        }

        $dump .= "SET FOREIGN_KEY_CHECKS=1;\n";

        file_put_contents($outputFile, $dump);
    }

    /**
     * إضافة ملفات storage + uploads إلى الـ zip.
     */
    private function addFiles(ZipArchive $zip): void
    {
        $this->addDirectoryToZip($zip, storage_path('app/public'), 'storage');

        if (file_exists(public_path('uploads'))) {
            $this->addDirectoryToZip($zip, public_path('uploads'), 'uploads');
        }
    }

    private function addDirectoryToZip(ZipArchive $zip, string $path, string $zipPath = ''): void
    {
        if (! file_exists($path)) {
            return;
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path),
            \RecursiveIteratorIterator::LEAVES_ONLY,
        );

        foreach ($files as $file) {
            if ($file->isDir()) {
                continue;
            }

            $filePath = $file->getRealPath();
            $relativePath = $zipPath . '/' . substr($filePath, strlen($path) + 1);
            $zip->addFile($filePath, $relativePath);
        }
    }

    private function deleteDirectory(string $path): bool
    {
        if (! is_dir($path)) {
            return false;
        }

        $files = array_diff(scandir($path), ['.', '..']);

        foreach ($files as $file) {
            $full = $path . '/' . $file;
            is_dir($full) ? $this->deleteDirectory($full) : @unlink($full);
        }

        return @rmdir($path);
    }
}
