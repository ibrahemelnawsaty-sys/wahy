<?php

namespace App\Http\Controllers;

use App\Exports\ActivitiesExport;
use App\Exports\StudentsExport;
use App\Imports\StudentsImport;
use App\Models\PageBuilder;
use App\Models\QuestionBank;
use App\Models\School;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Activitylog\Models\Activity as ActivityLog;

class SuperAdminController extends Controller
{
    /**
     * عرض صفحة النسخ الاحتياطي
     */
    public function backups()
    {
        $backups = $this->getBackupsList();

        return view('super-admin.backups', compact('backups'));
    }

    /**
     * إنشاء نسخة احتياطية
     */
    public function createBackup(Request $request)
    {
        try {
            $type = $request->input('type', 'full');
            app(\App\Services\Backup\BackupService::class)->create($type);

            return redirect()->route('admin.backups')
                ->with('success', 'تم إنشاء النسخة الاحتياطية بنجاح');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Backup creation failed', [
                'error' => $e->getMessage(),
                'type' => $request->input('type', 'full'),
            ]);

            return redirect()->route('admin.backups')
                ->with('error', 'حدث خطأ أثناء إنشاء النسخة الاحتياطية');
        }
    }

    /**
     * إنشاء dump لقاعدة بيانات MySQL
     */
    private function createMySQLDump($zip, $timestamp)
    {
        $host = config('database.connections.mysql.host');
        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');

        $dumpFile = storage_path('app/temp-dump-' . $timestamp . '.sql');

        // استخدام mysqldump إذا كان متاحاً
        $command = sprintf(
            'mysqldump --host=%s --user=%s --password=%s %s > %s',
            escapeshellarg($host),
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($database),
            escapeshellarg($dumpFile),
        );

        // محاولة التشغيل
        exec($command, $output, $returnVar);

        // إذا فشل mysqldump، استخدم PHP dump
        if ($returnVar !== 0 || ! file_exists($dumpFile)) {
            $this->createPHPMySQLDump($dumpFile);
        }

        if (file_exists($dumpFile)) {
            $zip->addFile($dumpFile, 'database/mysql-dump.sql');
            // حذف الملف المؤقت بعد الإضافة
            register_shutdown_function(function () use ($dumpFile) {
                if (file_exists($dumpFile)) {
                    @unlink($dumpFile);
                }
            });
        }
    }

    /**
     * إنشاء MySQL dump باستخدام PHP
     */
    private function createPHPMySQLDump($outputFile)
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
                    $rowData = array_map(function ($value) {
                        return is_null($value) ? 'NULL' : "'" . addslashes($value) . "'";
                    }, (array) $row);
                    $values[] = '(' . implode(',', $rowData) . ')';
                }
                $dump .= implode(",\n", $values) . ";\n\n";
            }
        }

        $dump .= "SET FOREIGN_KEY_CHECKS=1;\n";

        file_put_contents($outputFile, $dump);
    }

    /**
     * إضافة ملفات إلى ZIP بشكل متكرر
     */
    private function addFilesToZip($zip, $path, $zipPath = '')
    {
        if (! file_exists($path)) {
            return;
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path),
            \RecursiveIteratorIterator::LEAVES_ONLY,
        );

        foreach ($files as $file) {
            if (! $file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = $zipPath . '/' . substr($filePath, strlen($path) + 1);
                $zip->addFile($filePath, $relativePath);
            }
        }
    }

    /**
     * تحميل نسخة احتياطية
     */
    public function downloadBackup($filename)
    {
        // حماية ضد Path Traversal: basename فقط + allowlist
        $safeName = basename($filename);
        if (! preg_match('/^[A-Za-z0-9_\-\.]+\.zip$/', $safeName)) {
            return redirect()->route('admin.backups')
                ->with('error', 'اسم الملف غير صالح');
        }

        $backupDir = realpath(storage_path('app/Laravel'));
        $path = realpath($backupDir . DIRECTORY_SEPARATOR . $safeName);

        if (! $path || ! $backupDir || strpos($path, $backupDir . DIRECTORY_SEPARATOR) !== 0) {
            return redirect()->route('admin.backups')
                ->with('error', 'الملف غير موجود');
        }

        if (! file_exists($path) || ! is_file($path)) {
            return redirect()->route('admin.backups')
                ->with('error', 'الملف غير موجود');
        }

        return response()->download($path, $safeName);
    }

    /**
     * حذف نسخة احتياطية — delegate إلى BackupService.
     */
    public function deleteBackup($filename)
    {
        // حماية ضد Path Traversal
        $safeName = basename($filename);
        if (! preg_match('/^[A-Za-z0-9_\-\.]+\.zip$/', $safeName)) {
            return redirect()->route('admin.backups')
                ->with('error', 'اسم الملف غير صالح');
        }

        try {
            $deleted = app(\App\Services\Backup\BackupService::class)->delete($safeName);

            if (! $deleted) {
                return redirect()->route('admin.backups')
                    ->with('error', 'النسخة الاحتياطية غير موجودة أو فشل الحذف: ' . $safeName);
            }

            return redirect()->route('admin.backups')
                ->with('success', 'تم حذف النسخة الاحتياطية بنجاح: ' . $safeName);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Backup deletion failed', ['error' => $e->getMessage()]);

            return redirect()->route('admin.backups')
                ->with('error', 'حدث خطأ أثناء حذف النسخة');
        }
    }

    /**
     * استرداد نسخة احتياطية
     */
    public function restoreBackup(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|mimes:zip',
        ]);

        try {
            $file = $request->file('backup_file');
            $extractPath = storage_path('app/backup-restore');

            // إنشاء مجلد مؤقت للاستخراج
            if (! file_exists($extractPath)) {
                mkdir($extractPath, 0755, true);
            }

            // استخراج الملف — مع حماية ZIP Slip
            $zip = new \ZipArchive;
            if ($zip->open($file->path()) === true) {
                $safeExtractRoot = realpath($extractPath);
                if (! $safeExtractRoot) {
                    $zip->close();
                    throw new \Exception('فشل تحضير مجلد الاستخراج');
                }

                // التحقق من كل ملف داخل الأرشيف قبل الاستخراج
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $entryName = $zip->getNameIndex($i);
                    if ($entryName === false) {
                        continue;
                    }

                    // رفض المسارات المطلقة و ../ و \ على Windows
                    if (preg_match('#(^/|^\\\\|^[A-Za-z]:|\.\.[\\\\/])#', $entryName)) {
                        $zip->close();
                        throw new \Exception('الأرشيف يحتوي على مسار غير آمن: ' . $entryName);
                    }

                    $targetPath = $safeExtractRoot . DIRECTORY_SEPARATOR . $entryName;
                    $normalizedTarget = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $targetPath);

                    // ضمان أن المسار النهائي داخل مجلد الاستخراج
                    if (strpos($normalizedTarget, $safeExtractRoot . DIRECTORY_SEPARATOR) !== 0
                        && $normalizedTarget !== $safeExtractRoot) {
                        $zip->close();
                        throw new \Exception('محاولة كتابة خارج مجلد الاستخراج: ' . $entryName);
                    }
                }

                $zip->extractTo($extractPath);
                $zip->close();

                $dbDriver = config('database.default');

                if ($dbDriver === 'sqlite') {
                    // استرداد SQLite
                    $dbBackupPath = $extractPath . '/database/database.sqlite';
                    if (! file_exists($dbBackupPath)) {
                        $dbBackupPath = $extractPath . '/database.sqlite';
                    }

                    if (file_exists($dbBackupPath)) {
                        $database = database_path('database.sqlite');

                        // نسخ احتياطي للقاعدة الحالية
                        copy($database, $database . '.backup.' . date('Y-m-d_H-i-s'));

                        // استرداد القاعدة الجديدة
                        copy($dbBackupPath, $database);
                    }
                } else {
                    // استرداد MySQL
                    $sqlBackupPath = $extractPath . '/database/mysql-dump.sql';
                    if (file_exists($sqlBackupPath)) {
                        // نسخ احتياطي للقاعدة الحالية أولاً
                        $backupZip = new \ZipArchive;
                        $preRestoreBackup = storage_path('app/Laravel/pre-restore-backup-' . date('Y-m-d_H-i-s') . '.zip');
                        if ($backupZip->open($preRestoreBackup, \ZipArchive::CREATE) === true) {
                            $this->createMySQLDump($backupZip, date('Y-m-d_H-i-s'));
                            $backupZip->close();
                        }

                        // استرداد من SQL
                        $this->restoreMySQLDump($sqlBackupPath);
                    }
                }

                // استرداد الملفات
                $storageBackupPath = $extractPath . '/storage';
                if (file_exists($storageBackupPath)) {
                    $this->copyDirectory($storageBackupPath, storage_path('app/public'));
                }

                $uploadsBackupPath = $extractPath . '/uploads';
                if (file_exists($uploadsBackupPath)) {
                    $this->copyDirectory($uploadsBackupPath, public_path('uploads'));
                }

                // تنظيف المجلد المؤقت
                $this->deleteDirectory($extractPath);

                return redirect()->route('admin.backups')
                    ->with('success', 'تم استرداد النسخة الاحتياطية بنجاح');
            } else {
                throw new \Exception('فشل فتح ملف ZIP');
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Backup restore failed', ['error' => $e->getMessage()]);

            return redirect()->route('admin.backups')
                ->with('error', 'حدث خطأ أثناء الاسترداد');
        }
    }

    /**
     * استرداد MySQL من ملف SQL
     */
    private function restoreMySQLDump($sqlFile)
    {
        // الطريقة الصحيحة: تمرير ملف الـ dump إلى أداة mysql عبر stdin.
        // explode(';') السابق كان يكسر أي استعلام يحتوي على ';' داخل نص → خطر تلف بيانات.
        $conn = config('database.default');
        $db = config("database.connections.{$conn}");

        $host = $db['host'] ?? '127.0.0.1';
        $port = (string) ($db['port'] ?? '3306');
        $user = $db['username'] ?? 'root';
        $pass = (string) ($db['password'] ?? '');
        $name = $db['database'] ?? '';

        $cmd = sprintf(
            'mysql --host=%s --port=%s --user=%s %s',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($user),
            escapeshellarg($name),
        );

        $descriptor = [
            0 => ['file', $sqlFile, 'r'], // stdin من ملف الـ dump كاملاً (لا تقسيم)
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        // تمرير كلمة المرور عبر متغيّر البيئة MYSQL_PWD لا عبر سطر الأوامر (أكثر أماناً)
        $env = array_merge($_ENV ?? [], ['MYSQL_PWD' => $pass]);

        $process = @proc_open($cmd, $descriptor, $pipes, base_path(), $env);
        if (! is_resource($process)) {
            throw new \RuntimeException('تعذّر تشغيل أداة mysql للاسترداد — تأكد من توفرها على الخادم.');
        }

        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exitCode = proc_close($process);

        if ($exitCode !== 0) {
            throw new \RuntimeException('فشل استرداد قاعدة البيانات: ' . trim((string) $stderr));
        }
    }

    /**
     * تشغيل تنظيف النسخ القديمة
     */
    public function cleanupBackups()
    {
        try {
            $backupPath = storage_path('app/Laravel');

            if (! file_exists($backupPath)) {
                return redirect()->route('admin.backups')
                    ->with('info', 'لا توجد نسخ احتياطية للتنظيف');
            }

            $files = scandir($backupPath);
            $deleted = 0;
            $thirtyDaysAgo = time() - (30 * 24 * 60 * 60); // 30 يوم

            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..') {
                    $fullPath = $backupPath . '/' . $file;
                    // حذف الملفات الأقدم من 30 يوم
                    if (filemtime($fullPath) < $thirtyDaysAgo) {
                        unlink($fullPath);
                        $deleted++;
                    }
                }
            }

            return redirect()->route('admin.backups')
                ->with('success', "تم تنظيف النسخ القديمة بنجاح. تم حذف {$deleted} ملف");
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Backup cleanup failed', ['error' => $e->getMessage()]);

            return redirect()->route('admin.backups')
                ->with('error', 'حدث خطأ أثناء التنظيف');
        }
    }

    /**
     * الحصول على قائمة النسخ الاحتياطية
     */
    private function getBackupsList()
    {
        $backupPath = storage_path('app/Laravel');

        if (! file_exists($backupPath)) {
            return [];
        }

        $files = scandir($backupPath);
        $backups = [];

        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $fullPath = $backupPath . '/' . $file;
                $backups[] = [
                    'name' => $file,
                    'size' => $this->formatBytes(filesize($fullPath)),
                    'date' => date('Y-m-d H:i:s', filemtime($fullPath)),
                    'timestamp' => filemtime($fullPath),
                ];
            }
        }

        // ترتيب حسب الأحدث
        usort($backups, function ($a, $b) {
            return $b['timestamp'] - $a['timestamp'];
        });

        return $backups;
    }

    /**
     * تنسيق حجم الملف
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * حذف مجلد بشكل تكراري
     */
    private function deleteDirectory($dir)
    {
        if (! file_exists($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }

        rmdir($dir);
    }

    /**
     * نسخ مجلد بشكل تكراري
     */
    private function copyDirectory($source, $destination)
    {
        if (! file_exists($destination)) {
            mkdir($destination, 0755, true);
        }

        $files = array_diff(scandir($source), ['.', '..']);
        foreach ($files as $file) {
            $sourcePath = $source . '/' . $file;
            $destPath = $destination . '/' . $file;

            if (is_dir($sourcePath)) {
                $this->copyDirectory($sourcePath, $destPath);
            } else {
                copy($sourcePath, $destPath);
            }
        }
    }

    /**
     * عرض سجل الأنشطة
     */
    public function activityLogs(Request $request)
    {
        $query = ActivityLog::with(['causer', 'subject'])
            ->latest();

        // Filter by model type
        if ($request->has('model') && $request->model) {
            $query->where('subject_type', $request->model);
        }

        // Filter by event type
        if ($request->has('event') && $request->event) {
            $query->where('event', $request->event);
        }

        // Filter by user
        if ($request->has('user_id') && $request->user_id) {
            $query->where('causer_id', $request->user_id);
        }

        // Filter by date range
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(50);

        // Get unique models for filter
        $models = ActivityLog::select('subject_type')
            ->distinct()
            ->pluck('subject_type')
            ->filter()
            ->map(function ($model) {
                return [
                    'value' => $model,
                    'label' => class_basename($model),
                ];
            });

        // Get users for filter
        $users = User::select('id', 'name', 'role')->get();

        return view('super-admin.activity-logs', compact('logs', 'models', 'users'));
    }

    /**
     * حذف سجلات قديمة
     */
    public function cleanActivityLogs(Request $request)
    {
        try {
            $days = $request->input('days', 30);
            $deleted = ActivityLog::where('created_at', '<', now()->subDays($days))->delete();

            return redirect()->route('super-admin.activity-logs')
                ->with('success', "تم حذف {$deleted} سجل أقدم من {$days} يوم");
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Activity logs cleanup failed', ['error' => $e->getMessage()]);

            return redirect()->route('super-admin.activity-logs')
                ->with('error', 'حدث خطأ أثناء الحذف');
        }
    }

    /**
     * عرض توثيق API
     */
    public function apiDocumentation()
    {
        return view('super-admin.api-documentation');
    }

    /**
     * عرض صفحة إدارة Excel
     */
    public function excelManagement()
    {
        return view('super-admin.excel-management');
    }

    /**
     * تصدير الطلاب إلى Excel
     */
    public function exportStudents(Request $request)
    {
        $schoolId = $request->input('school_id');
        $filename = 'students_' . date('Y-m-d_H-i-s') . '.xlsx';

        return Excel::download(new StudentsExport($schoolId), $filename);
    }

    /**
     * تصدير الأنشطة إلى Excel
     */
    public function exportActivities(Request $request)
    {
        $schoolId = $request->input('school_id');
        $filename = 'activities_' . date('Y-m-d_H-i-s') . '.xlsx';

        return Excel::download(new ActivitiesExport($schoolId), $filename);
    }

    /**
     * تصدير المعلمين إلى Excel
     */
    public function exportTeachers(Request $request)
    {
        $schoolId = $request->input('school_id');
        $filename = 'teachers_' . date('Y-m-d_H-i-s') . '.xlsx';

        return Excel::download(new \App\Exports\TeachersExport($schoolId), $filename);
    }

    /**
     * تصدير أولياء الأمور إلى Excel
     */
    public function exportParents(Request $request)
    {
        $schoolId = $request->input('school_id');
        $filename = 'parents_' . date('Y-m-d_H-i-s') . '.xlsx';

        return Excel::download(new \App\Exports\ParentsExport($schoolId), $filename);
    }

    /**
     * تصدير المدارس إلى Excel
     */
    public function exportSchools()
    {
        $filename = 'schools_' . date('Y-m-d_H-i-s') . '.xlsx';

        return Excel::download(new \App\Exports\SchoolsExport, $filename);
    }

    /**
     * استيراد الطلاب من Excel
     */
    public function importStudents(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
            'school_id' => 'required|exists:schools,id',
        ]);

        try {
            $import = new StudentsImport($request->school_id);
            Excel::import($import, $request->file('file'));

            $imported = $import->getImportedCount();
            $skipped = $import->getSkippedCount();
            $errors = $import->getErrors();

            if ($imported === 0 && $skipped > 0) {
                return redirect()->route('admin.excel-management')
                    ->with('error', "لم يتم استيراد أي طالب. تم تخطي {$skipped} صف (بريد مكرر أو بيانات ناقصة).");
            }

            $message = "تم استيراد {$imported} طالب بنجاح!";
            if ($skipped > 0) {
                $message .= " (تم تخطي {$skipped} صف مكرر أو فارغ)";
            }

            return redirect()->route('admin.excel-management')
                ->with('success', $message);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Student import failed', ['error' => $e->getMessage()]);

            return redirect()->route('admin.excel-management')
                ->with('error', 'حدث خطأ أثناء الاستيراد');
        }
    }

    /**
     * تنزيل قالب Excel للطلاب
     */
    public function downloadStudentsTemplate()
    {
        $headers = ['الاسم', 'البريد الإلكتروني', 'كلمة المرور', 'الهاتف', 'تاريخ الميلاد'];
        $sample = ['أحمد محمد', 'student@example.com', '123456', '0501234567', '2010-05-15'];

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        // Headers
        $sheet->fromArray($headers, null, 'A1');
        $sheet->fromArray($sample, null, 'A2');

        // Style headers
        $sheet->getStyle('A1:E1')->getFont()->setBold(true);
        $sheet->getStyle('A1:E1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('4F81BD');
        $sheet->getStyle('A1:E1')->getFont()->getColor()->setRGB('FFFFFF');

        // Auto width
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'students_template.xlsx';
        $temp_file = tempnam(sys_get_temp_dir(), $filename);

        $writer->save($temp_file);

        return response()->download($temp_file, $filename)->deleteFileAfterSend(true);
    }

    /**
     * بنك الأسئلة - الأسئلة المعلقة للموافقة
     */
    public function questionBank(Request $request)
    {
        $query = QuestionBank::with(['creator', 'lesson.concept.value', 'approver'])
            ->orderBy('created_at', 'desc');

        // Filter by status if provided
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $questions = $query->paginate(50);

        // إحصائيات
        $stats = [
            'total' => QuestionBank::count(),
            'pending' => QuestionBank::where('status', 'pending')->count(),
            'approved' => QuestionBank::where('status', 'approved')->count(),
            'rejected' => QuestionBank::where('status', 'rejected')->count(),
        ];

        return view('super-admin.question-bank', compact('questions', 'stats'));
    }

    /**
     * الموافقة على سؤال
     */
    public function approveQuestion(Request $request, $id)
    {
        $user = Auth::user();
        $question = QuestionBank::findOrFail($id);

        $question->approve($user->id);

        // تحديث نقاط المعلم
        if ($question->created_by) {
            \App\Models\TeacherPoint::updateTeacherPoints($question->created_by);
        }

        // إرسال إشعار للمعلم
        if ($question->created_by) {
            \App\Services\NotificationService::create(
                $question->created_by,
                'question_approved',
                '✅ تمت الموافقة على سؤالك',
                "تمت الموافقة على سؤالك: {$question->title}",
                route('teacher.question-bank.index'),
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'تمت الموافقة على السؤال بنجاح',
        ]);
    }

    /**
     * رفض سؤال
     */
    public function rejectQuestion(Request $request, $id)
    {
        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();
        $question = QuestionBank::findOrFail($id);

        $question->reject($user->id, $request->reason);

        // إرسال إشعار للمعلم
        \App\Services\NotificationService::create(
            $question->created_by,
            'question_rejected',
            '❌ تم رفض سؤالك',
            "تم رفض سؤالك: {$question->title}" . ($request->reason ? ". السبب: {$request->reason}" : ''),
            route('teacher.question-bank.index'),
        );

        return response()->json([
            'success' => true,
            'message' => 'تم رفض السؤال بنجاح',
        ]);
    }

    /**
     * إضافة سؤال جديد لبنك الأسئلة (من الأدمن)
     */
    public function storeQuestion(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'question_text' => 'required|string',
            'question_type' => 'required|in:multiple_choice,true_false,short_answer,essay',
            'difficulty' => 'required|in:easy,medium,hard',
            'points' => 'required|integer|min:1|max:100',
            'correct_answer' => 'nullable|string',
            'explanation' => 'nullable|string',
            'options' => 'nullable|array',
            'options.*.text' => 'nullable|string',
            'options.*.is_correct' => 'nullable|boolean',
        ]);

        $user = Auth::user();

        // تنظيف الخيارات
        $options = null;
        if ($validated['question_type'] === 'multiple_choice' && ! empty($validated['options'])) {
            $options = collect($validated['options'])->filter(fn ($o) => ! empty($o['text']))->values()->toArray();
        }

        $question = QuestionBank::create([
            'created_by' => $user->id,
            'title' => $validated['title'],
            'question_text' => $validated['question_text'],
            'question_type' => $validated['question_type'],
            'difficulty' => $validated['difficulty'],
            'points' => $validated['points'],
            'correct_answer' => $validated['correct_answer'] ?? null,
            'explanation' => $validated['explanation'] ?? null,
            'options' => $options,
            'status' => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        return redirect()->route('admin.question-bank.index')
            ->with('success', "تم إضافة السؤال \"{$question->title}\" بنجاح!");
    }

    // ============== إدارة تحديات PvP ==============

    /**
     * قائمة تحديات PvP
     */
    public function pvpChallenges(Request $request)
    {
        $challenges = \App\Models\PvpChallenge::with(['creator', 'value'])
            ->withCount(['matches'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('super-admin.pvp-challenges.index', compact('challenges'));
    }

    /**
     * نموذج إنشاء تحدي PvP
     */
    public function createPvpChallenge()
    {
        $approvedQuestions = \App\Models\QuestionBank::where('status', 'approved')
            ->orderBy('title')
            ->get(['id', 'title', 'question_type', 'difficulty']);

        // القيم المتاحة لربط التحدي (يمكن أن يكون التحدي بلا قيمة = عام)
        $values = \App\Models\Value::where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('super-admin.pvp-challenges.create', compact('approvedQuestions', 'values'));
    }

    /**
     * حفظ تحدي PvP جديد
     */
    public function storePvpChallenge(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'value_id' => 'nullable|integer|exists:values,id',
            'time_limit' => 'required|integer|min:30|max:1800',
            'difficulty' => 'nullable|in:easy,medium,hard',
            'questions_json' => 'required|string',
            'is_active' => 'nullable|boolean',
        ]);

        // فكّ وبناء الأسئلة المُنشأة inline (مشترك بين الإنشاء والتحديث)
        $build = $this->buildPvpQuestions($validated['questions_json']);
        if (isset($build['error'])) {
            return back()->withInput()->withErrors(['questions_json' => $build['error']]);
        }
        $questions = $build['questions'];

        $challenge = \App\Models\PvpChallenge::create([
            'title' => $validated['title'],
            'value_id' => $validated['value_id'] ?? null,
            'time_limit' => (int) $validated['time_limit'],
            'difficulty' => $validated['difficulty'] ?? 'medium',
            'questions' => $questions,
            'is_active' => (bool) ($validated['is_active'] ?? true),
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('admin.pvp-challenges.index')
            ->with('success', "تم إنشاء التحدي \"{$challenge->title}\" بنجاح");
    }

    /**
     * تبديل حالة تفعيل التحدي
     */
    public function togglePvpChallenge($id)
    {
        $challenge = \App\Models\PvpChallenge::findOrFail($id);
        $challenge->update(['is_active' => ! $challenge->is_active]);

        return back()->with('success', $challenge->is_active ? 'تم تفعيل التحدي' : 'تم تعطيل التحدي');
    }

    /**
     * بناء مصفوفة الأسئلة من questions_json — مشتركة بين الإنشاء والتحديث.
     * تُعيد ['questions'=>[...]] أو ['error'=>'رسالة'].
     */
    private function buildPvpQuestions(?string $json): array
    {
        $raw = json_decode((string) $json, true);
        if (! is_array($raw) || count($raw) === 0) {
            return ['error' => 'أضِف سؤالًا واحدًا على الأقل'];
        }
        if (count($raw) > 50) {
            return ['error' => 'الحد الأقصى 50 سؤالًا'];
        }

        $questions = [];
        foreach ($raw as $i => $q) {
            $n = $i + 1;
            $text = trim((string) ($q['text'] ?? ''));
            if ($text === '') {
                return ['error' => "السؤال #{$n}: النص مطلوب"];
            }
            $type = in_array(($q['type'] ?? ''), ['multiple_choice', 'true_false'], true) ? $q['type'] : 'multiple_choice';
            $points = max(1, min(1000, (int) ($q['points'] ?? 100)));

            if ($type === 'true_false') {
                $correct = (($q['correct'] ?? 'true') === 'false') ? 'false' : 'true';
                $questions[] = ['text' => $text, 'type' => 'true_false', 'options' => [], 'correct' => $correct, 'points' => $points];
            } else {
                $options = [];
                foreach ((array) ($q['options'] ?? []) as $opt) {
                    $t = trim((string) (is_array($opt) ? ($opt['text'] ?? '') : $opt));
                    if ($t !== '') {
                        $options[] = ['text' => $t];
                    }
                }
                $correct = (int) ($q['correct'] ?? -1);
                if (count($options) < 2 || $correct < 0 || $correct >= count($options)) {
                    return ['error' => "السؤال #{$n}: يحتاج خيارين على الأقل مع تحديد الإجابة الصحيحة"];
                }
                $questions[] = ['text' => $text, 'type' => 'multiple_choice', 'options' => $options, 'correct' => $correct, 'points' => $points];
            }
        }

        return ['questions' => $questions];
    }

    /**
     * نموذج تعديل تحدي PvP (يعيد استخدام نموذج الإنشاء)
     */
    public function editPvpChallenge($id)
    {
        $challenge = \App\Models\PvpChallenge::findOrFail($id);

        $values = \App\Models\Value::where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name']);

        // بذور المنشئ بصيغة النموذج (تُحوّل التحديات القديمة إلى الصيغة الجديدة عند الحفظ)
        $seedQuestions = $challenge->normalizedQuestions()->map(function ($q) {
            return [
                'text' => $q['text'],
                'type' => $q['type'],
                'options' => $q['options'],
                'correct' => $q['correct'],
                'points' => $q['points'],
            ];
        })->values();

        return view('super-admin.pvp-challenges.create', compact('challenge', 'values', 'seedQuestions'));
    }

    /**
     * تحديث تحدي PvP
     */
    public function updatePvpChallenge(Request $request, $id)
    {
        $challenge = \App\Models\PvpChallenge::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'value_id' => 'nullable|integer|exists:values,id',
            'time_limit' => 'required|integer|min:30|max:1800',
            'difficulty' => 'nullable|in:easy,medium,hard',
            'questions_json' => 'required|string',
            'is_active' => 'nullable|boolean',
        ]);

        $build = $this->buildPvpQuestions($validated['questions_json']);
        if (isset($build['error'])) {
            return back()->withInput()->withErrors(['questions_json' => $build['error']]);
        }

        $challenge->update([
            'title' => $validated['title'],
            'value_id' => $validated['value_id'] ?? null,
            'time_limit' => (int) $validated['time_limit'],
            'difficulty' => $validated['difficulty'] ?? 'medium',
            'questions' => $build['questions'],
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);

        return redirect()->route('admin.pvp-challenges.index')
            ->with('success', "تم تحديث التحدي \"{$challenge->title}\" بنجاح");
    }

    /**
     * حذف تحدي
     */
    public function destroyPvpChallenge($id)
    {
        $challenge = \App\Models\PvpChallenge::findOrFail($id);
        $title = $challenge->title;
        $challenge->delete();

        return redirect()->route('admin.pvp-challenges.index')
            ->with('success', "تم حذف التحدي \"{$title}\"");
    }

    /**
     * عرض صفحة إدارة الصفحة الرئيسية
     */
    public function landingPage()
    {
        // جلب إعدادات الثيم
        $themeSettings = [
            'site_name' => setting('site_name', 'نظام القيم'),
            'site_tagline' => setting('site_tagline', 'منصة تعليمية لبناء القيم'),
            'primary_color' => setting('primary_color', '#3CCB8A'),
            'secondary_color' => setting('secondary_color', '#3B82F6'),
            'font_family' => setting('font_family', 'IBM Plex Sans Arabic'),
            'site_logo' => setting('site_logo'),
            'site_favicon' => setting('site_favicon'),
        ];

        // جلب صفحة الـ Landing أو إنشاء واحدة افتراضية
        $landingPage = PageBuilder::where('slug', 'home')->first();

        if (! $landingPage) {
            $landingPage = PageBuilder::create([
                'page_name' => 'الصفحة الرئيسية',
                'slug' => 'home',
                'json_data' => [],
                'is_active' => true,
            ]);
        }

        return view('super-admin.landing-page', compact('themeSettings', 'landingPage'));
    }

    /**
     * تحديث إعدادات الثيم للصفحة الرئيسية
     */
    public function updateLandingTheme(Request $request)
    {
        $validated = $request->validate([
            'site_name' => 'nullable|string|max:255',
            'site_tagline' => 'nullable|string|max:255',
            'primary_color' => 'nullable|string|max:7',
            'secondary_color' => 'nullable|string|max:7',
            'font_family' => 'nullable|string|max:100',
        ]);

        foreach ($validated as $key => $value) {
            if ($value !== null) {
                Setting::set($key, $value);
            }
        }

        Setting::clearCache();

        return response()->json([
            'success' => true,
            'message' => 'تم حفظ إعدادات الثيم بنجاح!',
        ]);
    }

    /**
     * تحديث محتوى الصفحة الرئيسية
     */
    public function updateLandingContent(Request $request)
    {
        $validated = $request->validate([
            'json_data' => 'required|array',
        ]);

        $landingPage = PageBuilder::where('slug', 'home')->first();

        if (! $landingPage) {
            $landingPage = new PageBuilder;
            $landingPage->page_name = 'الصفحة الرئيسية';
            $landingPage->slug = 'home';
            $landingPage->is_active = true;
        }

        $landingPage->json_data = $validated['json_data'];
        $landingPage->save();

        return response()->json([
            'success' => true,
            'message' => 'تم حفظ محتوى الصفحة بنجاح!',
        ]);
    }

    /**
     * إضافة عنصر جديد للصفحة الرئيسية
     */
    public function addLandingBlock(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string|in:hero,heading,paragraph,button,stats,features,testimonials,cta,image,spacer',
            'content' => 'required|array',
            'position' => 'nullable|integer',
        ]);

        $landingPage = PageBuilder::where('slug', 'home')->firstOrFail();
        $blocks = $landingPage->json_data ?? [];

        $newBlock = [
            'id' => uniqid(),
            'type' => $validated['type'],
            'content' => $validated['content'],
        ];

        if (isset($validated['position'])) {
            array_splice($blocks, $validated['position'], 0, [$newBlock]);
        } else {
            $blocks[] = $newBlock;
        }

        $landingPage->json_data = $blocks;
        $landingPage->save();

        return response()->json([
            'success' => true,
            'message' => 'تم إضافة العنصر بنجاح!',
            'block' => $newBlock,
        ]);
    }

    /**
     * تحديث عنصر في الصفحة الرئيسية
     */
    public function updateLandingBlock(Request $request, $id)
    {
        $validated = $request->validate([
            'content' => 'required|array',
        ]);

        $landingPage = PageBuilder::where('slug', 'home')->firstOrFail();
        $blocks = $landingPage->json_data ?? [];

        $blockIndex = array_search($id, array_column($blocks, 'id'));

        if ($blockIndex === false) {
            return response()->json([
                'success' => false,
                'message' => 'العنصر غير موجود',
            ], 404);
        }

        $blocks[$blockIndex]['content'] = $validated['content'];
        $landingPage->json_data = $blocks;
        $landingPage->save();

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث العنصر بنجاح!',
            'block' => $blocks[$blockIndex],
        ]);
    }

    /**
     * حذف عنصر من الصفحة الرئيسية
     */
    public function deleteLandingBlock($id)
    {
        $landingPage = PageBuilder::where('slug', 'home')->firstOrFail();
        $blocks = $landingPage->json_data ?? [];

        $blockIndex = array_search($id, array_column($blocks, 'id'));

        if ($blockIndex === false) {
            return response()->json([
                'success' => false,
                'message' => 'العنصر غير موجود',
            ], 404);
        }

        array_splice($blocks, $blockIndex, 1);
        $landingPage->json_data = $blocks;
        $landingPage->save();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف العنصر بنجاح!',
        ]);
    }

    /**
     * إعادة ترتيب عناصر الصفحة الرئيسية
     */
    public function reorderLandingBlocks(Request $request)
    {
        $validated = $request->validate([
            'blocks' => 'required|array',
            'blocks.*.id' => 'required|string',
        ]);

        $landingPage = PageBuilder::where('slug', 'home')->firstOrFail();
        $oldBlocks = $landingPage->json_data ?? [];
        $newBlocks = [];

        // إعادة ترتيب العناصر حسب الترتيب الجديد
        foreach ($validated['blocks'] as $blockOrder) {
            $block = collect($oldBlocks)->firstWhere('id', $blockOrder['id']);
            if ($block) {
                $newBlocks[] = $block;
            }
        }

        $landingPage->json_data = $newBlocks;
        $landingPage->save();

        return response()->json([
            'success' => true,
            'message' => 'تم إعادة ترتيب العناصر بنجاح!',
        ]);
    }

    /**
     * استيراد المحتوى من الصفحة الحالية (landing.blade.php)
     */
    public function importCurrentLanding()
    {
        $landingPage = PageBuilder::where('slug', 'home')->firstOrFail();

        // تحويل محتوى landing.blade.php إلى blocks
        $blocks = $this->parseLandingPageToBlocks();

        $landingPage->json_data = $blocks;
        $landingPage->save();

        return response()->json([
            'success' => true,
            'message' => 'تم استيراد المحتوى من الصفحة الحالية بنجاح!',
            'blocks' => $blocks,
        ]);
    }

    /**
     * تحويل محتوى landing.blade.php إلى blocks
     */
    private function parseLandingPageToBlocks()
    {
        $blocks = [];

        // Hero Section
        $blocks[] = [
            'id' => 'block_hero_' . time(),
            'type' => 'hero',
            'content' => [
                'title' => 'منصة القيم المدرسية – تعليم يعيش مع الطلاب',
                'subtitle' => 'نبني القيم الإنسانية بطريقة تفاعلية وممتعة. منصة شاملة تربط المدرسة والمعلم والطالب وولي الأمر في بيئة تعليمية آمنة ومحفزة.',
                'buttonText' => 'ابدأ الآن',
                'buttonLink' => '/register',
                'secondaryButtonText' => 'اعرف المزيد',
                'secondaryButtonLink' => '#features',
            ],
        ];

        // Stats
        $blocks[] = [
            'id' => 'block_stats_' . time(),
            'type' => 'stats',
            'content' => [
                'items' => [
                    ['label' => 'مدرسة', 'value' => '500+'],
                    ['label' => 'طالب', 'value' => '50k+'],
                    ['label' => 'معلم', 'value' => '2k+'],
                ],
            ],
        ];

        // Heading
        $blocks[] = [
            'id' => 'block_heading_features_' . time(),
            'type' => 'heading',
            'content' => [
                'text' => 'لماذا قيمّ؟',
                'level' => 'h2',
            ],
        ];

        $blocks[] = [
            'id' => 'block_paragraph_features_' . time(),
            'type' => 'paragraph',
            'content' => [
                'text' => 'نظام متكامل بمميزات فريدة',
            ],
        ];

        // Features
        $blocks[] = [
            'id' => 'block_features_' . time(),
            'type' => 'features',
            'content' => [
                'items' => [
                    [
                        'title' => 'QR فريد لكل مستخدم',
                        'description' => 'كل طالب ومعلم لديه رمز QR خاص للدخول السريع وتسجيل الحضور والأنشطة',
                    ],
                    [
                        'title' => 'لوحة صدارة ذكية',
                        'description' => 'نظام تنافسي محفز يعرض أفضل الطلاب والفرق بناءً على الإنجازات والنقاط',
                    ],
                    [
                        'title' => 'اقتراح أنشطة بالذكاء الاصطناعي',
                        'description' => 'نظام ذكي يقترح أنشطة مخصصة لكل طالب حسب مستواه واهتماماته',
                    ],
                    [
                        'title' => 'متابعة وتقييم المعلمين',
                        'description' => 'أدوات شاملة لمتابعة أداء الطلاب وتقييمهم بطرق متنوعة ومرنة',
                    ],
                ],
            ],
        ];

        // Heading - القيم
        $blocks[] = [
            'id' => 'block_heading_values_' . time(),
            'type' => 'heading',
            'content' => [
                'text' => 'كيف نبني القيم؟',
                'level' => 'h2',
            ],
        ];

        $blocks[] = [
            'id' => 'block_paragraph_values_' . time(),
            'type' => 'paragraph',
            'content' => [
                'text' => 'منهجية متكاملة من القيمة إلى التطبيق العملي',
            ],
        ];

        // CTA
        $blocks[] = [
            'id' => 'block_cta_' . time(),
            'type' => 'cta',
            'content' => [
                'title' => 'جاهز للانضمام؟',
                'description' => 'ابدأ رحلتك اليوم',
                'buttonText' => 'ابدأ مجاناً',
                'buttonLink' => '/register',
            ],
        ];

        return $blocks;
    }

    // ==================== الأنشطة المميزة ====================

    /**
     * عرض الأنشطة المميزة
     */
    public function featuredActivities()
    {
        $activities = \App\Models\Activity::where('is_featured', true)
            ->with(['featuredBy', 'lesson.concept.value', 'creator'])
            ->latest('featured_at')
            ->paginate(20);

        $stats = [
            'total_featured' => \App\Models\Activity::where('is_featured', true)->count(),
            'this_month' => \App\Models\Activity::where('is_featured', true)
                ->whereMonth('featured_at', now()->month)
                ->whereYear('featured_at', now()->year)
                ->count(),
            'by_teachers' => \App\Models\Activity::where('is_featured', true)
                ->distinct('featured_by')
                ->count('featured_by'),
        ];

        return view('super-admin.featured-activities', compact('activities', 'stats'));
    }

    /**
     * عرض تفاصيل نشاط مميز
     */
    public function showFeaturedActivity($id)
    {
        $activity = \App\Models\Activity::where('is_featured', true)
            ->with(['featuredBy', 'lesson', 'creator', 'submissions'])
            ->findOrFail($id);

        return view('super-admin.featured-activity-details', compact('activity'));
    }

    /**
     * إلغاء تمييز نشاط
     */
    public function unfeatureActivity($id)
    {
        $activity = \App\Models\Activity::findOrFail($id);

        $activity->update([
            'is_featured' => false,
            'featured_by' => null,
            'featured_at' => null,
            'featured_reason' => null,
        ]);

        return back()->with('success', 'تم إلغاء تمييز النشاط بنجاح');
    }

    // ==================== المستخدمين النشطين أون لاين ====================

    /**
     * عرض صفحة المستخدمين النشطين
     */
    public function onlineUsers()
    {
        $data = $this->getOnlineUsersData();

        return view('admin.online-users', $data);
    }

    /**
     * API للتحديث التلقائي
     */
    public function onlineUsersApi()
    {
        $data = $this->getOnlineUsersData();

        return response()->json($data);
    }

    /**
     * جلب بيانات المستخدمين النشطين
     */
    private function getOnlineUsersData()
    {
        $onlineThreshold = now()->subMinutes(5)->timestamp;

        // جلب جميع الجلسات النشطة
        $activeSessions = DB::table('sessions')
            ->whereNotNull('user_id')
            ->where('last_activity', '>=', $onlineThreshold)
            ->join('users', 'sessions.user_id', '=', 'users.id')
            ->leftJoin('schools', 'users.school_id', '=', 'schools.id')
            ->select([
                'users.id',
                'users.name',
                'users.email',
                'users.role',
                'users.avatar',
                'users.status',
                'schools.name as school_name',
                DB::raw('MAX(sessions.last_activity) as last_activity'),
            ])
            ->groupBy('users.id', 'users.name', 'users.email', 'users.role', 'users.avatar', 'users.status', 'schools.name')
            ->orderBy('last_activity', 'desc')
            ->get()
            ->map(function ($user) {
                $minutesAgo = floor((now()->timestamp - $user->last_activity) / 60);
                $user->online_since = $minutesAgo <= 1 ? 'الآن' : "منذ {$minutesAgo} دقيقة";
                $user->role_ar = User::getRoleNameAr($user->role);
                $user->role_icon = User::getRoleIcon($user->role);

                return $user;
            });

        // تصنيف حسب الدور
        $roleNames = [
            'super_admin' => 'مدير النظام',
            'school_admin' => 'مديرو المدارس',
            'teacher' => 'المعلمين',
            'student' => 'الطلاب',
            'parent' => 'أولياء الأمور',
        ];

        $stats = [];
        foreach ($roleNames as $role => $label) {
            $stats[$role] = [
                'label' => $label,
                'count' => $activeSessions->where('role', $role)->count(),
            ];
        }

        return [
            'onlineUsers' => $activeSessions,
            'totalOnline' => $activeSessions->count(),
            'stats' => $stats,
        ];
    }

    // ==================== المراحل الدراسية ====================

    /**
     * عرض صفحة إدارة المراحل الدراسية
     */
    public function educationLevels()
    {
        $levels = \App\Models\EducationLevel::ordered()
            ->with(['academicYears' => fn ($q) => $q->ordered()])
            ->withCount('schools')
            ->get();

        $schools = School::select('id', 'name')->orderBy('name')->get();

        return view('admin.education-levels', compact('levels', 'schools'));
    }

    /**
     * إضافة مرحلة دراسية جديدة
     */
    public function storeLevel(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:education_levels,name',
        ]);

        $maxOrder = \App\Models\EducationLevel::max('sort_order') ?? 0;

        $level = \App\Models\EducationLevel::create([
            'name' => $validated['name'],
            'sort_order' => $maxOrder + 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إضافة المرحلة الدراسية بنجاح',
            'level' => $level,
        ]);
    }

    /**
     * تحديث مرحلة دراسية
     */
    public function updateLevel(Request $request, $id)
    {
        $level = \App\Models\EducationLevel::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:education_levels,name,' . $id,
            'status' => 'nullable|boolean',
        ]);

        $level->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث المرحلة الدراسية بنجاح',
        ]);
    }

    /**
     * حذف مرحلة دراسية
     */
    public function deleteLevel($id)
    {
        $level = \App\Models\EducationLevel::findOrFail($id);
        $level->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف المرحلة الدراسية بنجاح',
        ]);
    }

    /**
     * إضافة سنة دراسية جديدة
     */
    public function storeYear(Request $request)
    {
        $validated = $request->validate([
            'education_level_id' => 'required|exists:education_levels,id',
            'name' => 'required|string|max:255',
        ]);

        $maxOrder = \App\Models\AcademicYear::where('education_level_id', $validated['education_level_id'])->max('sort_order') ?? 0;

        $year = \App\Models\AcademicYear::create([
            'education_level_id' => $validated['education_level_id'],
            'name' => $validated['name'],
            'sort_order' => $maxOrder + 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إضافة السنة الدراسية بنجاح',
            'year' => $year,
        ]);
    }

    /**
     * تحديث سنة دراسية
     */
    public function updateYear(Request $request, $id)
    {
        $year = \App\Models\AcademicYear::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'nullable|boolean',
        ]);

        $year->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث السنة الدراسية بنجاح',
        ]);
    }

    /**
     * حذف سنة دراسية
     */
    public function deleteYear($id)
    {
        $year = \App\Models\AcademicYear::findOrFail($id);
        $year->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف السنة الدراسية بنجاح',
        ]);
    }

    /**
     * ربط المدرسة بالمراحل الدراسية
     */
    public function linkSchoolLevels(Request $request)
    {
        $validated = $request->validate([
            'school_id' => 'required|exists:schools,id',
            'education_level_ids' => 'required|array',
            'education_level_ids.*' => 'exists:education_levels,id',
        ]);

        $school = School::findOrFail($validated['school_id']);
        $school->educationLevels()->sync($validated['education_level_ids']);

        return response()->json([
            'success' => true,
            'message' => 'تم ربط المراحل الدراسية بالمدرسة بنجاح',
        ]);
    }
}
