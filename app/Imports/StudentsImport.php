<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Validators\Failure;

class StudentsImport implements SkipsEmptyRows, SkipsOnError, SkipsOnFailure, ToModel, WithStartRow
{
    protected $schoolId;

    protected $importedCount = 0;

    protected $skippedCount = 0;

    protected $errors = [];

    public function __construct($schoolId)
    {
        $this->schoolId = $schoolId;
    }

    /**
     * بدء القراءة من الصف الثاني (تخطي صف العناوين)
     */
    public function startRow(): int
    {
        return 2;
    }

    /**
     * القالب يحتوي على 5 أعمدة بالترتيب:
     * [0] الاسم
     * [1] البريد الإلكتروني
     * [2] كلمة المرور
     * [3] الهاتف
     * [4] تاريخ الميلاد
     */
    public function model(array $row)
    {
        // الحصول على القيم بحسب ترتيب الأعمدة
        $name = trim($row[0] ?? '');
        $email = trim($row[1] ?? '');
        $password = trim($row[2] ?? '') ?: '123456';
        $phone = trim($row[3] ?? '') ?: null;
        $birthDate = trim($row[4] ?? '') ?: null;

        // تخطي إذا لم يكن هناك اسم أو بريد إلكتروني
        if (empty($name) || empty($email)) {
            $this->skippedCount++;

            return null;
        }

        // التحقق من صحة البريد الإلكتروني
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->skippedCount++;
            $this->errors[] = "تم تخطي '{$name}': البريد الإلكتروني '{$email}' غير صالح";

            return null;
        }

        // تخطي إذا البريد موجود مسبقاً
        if (User::where('email', $email)->exists()) {
            $this->skippedCount++;

            return null;
        }

        $this->importedCount++;

        return new User([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 'student',
            'school_id' => $this->schoolId,
            'phone' => $phone,
            'birth_date' => $birthDate,
            'status' => 'active',
        ]);
    }

    public function onError(\Throwable $e)
    {
        $this->errors[] = $e->getMessage();
        Log::error('Student import error: ' . $e->getMessage());
    }

    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            $this->errors[] = "صف {$failure->row()}: " . implode(', ', $failure->errors());
        }
    }

    public function getImportedCount(): int
    {
        return $this->importedCount;
    }

    public function getSkippedCount(): int
    {
        return $this->skippedCount;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
