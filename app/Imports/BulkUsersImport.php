<?php

namespace App\Imports;

use App\Enums\UserRole;
use App\Models\Classroom;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class BulkUsersImport implements ToCollection, WithHeadingRow
{
    protected $schoolId;

    protected $role;

    protected $errors = [];

    protected $successCount = 0;

    public function __construct($schoolId, $role)
    {
        $this->schoolId = $schoolId;
        $this->role = $role;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            try {
                $rowNumber = $index + 3; // +3 لأن العنوان العربي في الصف 1 و Heading في الصف 2

                // تنظيف المفاتيح (إزالة المسافات)
                $rowArray = $row->toArray();
                $cleanRow = [];
                foreach ($rowArray as $key => $value) {
                    $cleanKey = trim($key);
                    $cleanRow[$cleanKey] = $value;
                }

                if ($this->role === 'students') {
                    $this->importStudent($cleanRow, $rowNumber);
                } elseif ($this->role === 'teachers') {
                    $this->importTeacher($cleanRow, $rowNumber);
                } elseif ($this->role === 'parents') {
                    $this->importParent($cleanRow, $rowNumber);
                }
            } catch (\Exception $e) {
                $this->errors[] = [
                    'row' => $rowNumber,
                    'message' => $e->getMessage(),
                ];
            }
        }
    }

    protected function importStudent($row, $rowNumber)
    {
        // دعم كلا من العربية والإنجليزية
        $name = trim($row['name'] ?? $row['الاسم'] ?? '');
        $email = trim($row['email'] ?? $row['البريد الإلكتروني'] ?? '');
        $phone = trim($row['phone'] ?? $row['الهاتف'] ?? '');
        $birthDate = trim($row['birth_date'] ?? $row['تاريخ الميلاد (YYYY-MM-DD)'] ?? $row['تاريخ الميلاد'] ?? '');
        $classroomName = trim($row['classroom'] ?? $row['الفصل'] ?? '');

        if (empty($name) || empty($email)) {
            throw new \Exception('الاسم والبريد الإلكتروني مطلوبان');
        }

        // التحقق من عدم وجود البريد الإلكتروني
        if (User::where('email', $email)->exists()) {
            throw new \Exception("البريد الإلكتروني {$email} موجود مسبقاً");
        }

        // إنشاء الطالب
        $student = User::create($this->buildUserAttributes(
            UserRole::Student,
            $name,
            $email,
            $phone,
            'STU',
            ['birth_date' => $birthDate ?: null],
        ));

        // إضافة الطالب للفصل إذا كان موجوداً
        if ($classroomName) {
            $classroom = Classroom::where('school_id', $this->schoolId)
                ->where('name', $classroomName)
                ->first();

            if ($classroom) {
                $student->classrooms()->attach($classroom->id, [
                    'enrollment_date' => now(),
                    'status' => 'active',
                ]);
            }
        }

        $this->successCount++;
    }

    protected function importTeacher($row, $rowNumber)
    {
        // دعم كلا من العربية والإنجليزية
        $name = trim($row['name'] ?? $row['الاسم'] ?? '');
        $email = trim($row['email'] ?? $row['البريد الإلكتروني'] ?? '');
        $phone = trim($row['phone'] ?? $row['الهاتف'] ?? '');
        $alternativeEmail = trim($row['alternative_email'] ?? $row['البريد الإلكتروني البديل'] ?? '');

        if (empty($name) || empty($email)) {
            throw new \Exception('الاسم والبريد الإلكتروني مطلوبان');
        }

        // التحقق من عدم وجود البريد الإلكتروني
        if (User::where('email', $email)->exists()) {
            throw new \Exception("البريد الإلكتروني {$email} موجود مسبقاً");
        }

        // إنشاء المعلم
        User::create($this->buildUserAttributes(
            UserRole::Teacher,
            $name,
            $email,
            $phone,
            'TCH',
        ));

        $this->successCount++;
    }

    protected function importParent($row, $rowNumber)
    {
        // دعم كلا من العربية والإنجليزية
        $name = trim($row['name'] ?? $row['الاسم'] ?? '');
        $email = trim($row['email'] ?? $row['البريد الإلكتروني'] ?? '');
        $phone = trim($row['phone'] ?? $row['الهاتف'] ?? '');
        $childrenNames = trim($row['children'] ?? $row['اسم الطالب (أو أكثر، مفصولة بفاصلة)'] ?? $row['اسم الطالب'] ?? '');

        if (empty($name) || empty($email)) {
            throw new \Exception('الاسم والبريد الإلكتروني مطلوبان');
        }

        // التحقق من عدم وجود البريد الإلكتروني
        if (User::where('email', $email)->exists()) {
            throw new \Exception("البريد الإلكتروني {$email} موجود مسبقاً");
        }

        // إنشاء ولي الأمر
        $parent = User::create($this->buildUserAttributes(
            UserRole::Parent,
            $name,
            $email,
            $phone,
            'PAR',
        ));

        // ربط الأبناء
        if ($childrenNames) {
            $childrenNamesArray = array_map('trim', explode(',', $childrenNames));

            foreach ($childrenNamesArray as $childName) {
                $child = User::where('school_id', $this->schoolId)
                    ->where('role', UserRole::Student->value)
                    ->where('name', $childName)
                    ->first();

                if ($child) {
                    $parent->children()->syncWithoutDetaching([$child->id => ['relationship' => 'parent']]);
                }
            }
        }

        $this->successCount++;
    }

    /**
     * Helper مشترك لبناء سمات user جديد — يقلل التكرار في 3 methods.
     *
     * @param  string  $qrPrefix  STU / TCH / PAR
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    private function buildUserAttributes(UserRole $role, string $name, string $email, string $phone, string $qrPrefix, array $extra = []): array
    {
        return array_merge([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make('123456'), // كلمة مرور افتراضية — على الطالب تغييرها
            'role' => $role->value,
            'school_id' => $this->schoolId,
            'phone' => $phone ?: null,
            'status' => 'active',
            'qr_code' => $qrPrefix . '-' . strtoupper(uniqid()),
            'password_change_required' => true,
        ], $extra);
    }

    /**
     * تحديد صف العناوين (الصف 2 لأن الصف 1 عناوين عربية)
     */
    public function headingRow(): int
    {
        return 2;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getSuccessCount()
    {
        return $this->successCount;
    }
}
