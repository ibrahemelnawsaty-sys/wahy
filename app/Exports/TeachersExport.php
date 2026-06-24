<?php

namespace App\Exports;

use App\Exports\Concerns\SanitizesCsvOutput;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class TeachersExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    use SanitizesCsvOutput;

    protected $schoolId;

    public function __construct($schoolId = null)
    {
        $this->schoolId = $schoolId;
    }

    public function collection()
    {
        $query = User::with(['school', 'teachingClassrooms'])
            ->where('role', 'teacher');

        if ($this->schoolId) {
            $query->where('school_id', $this->schoolId);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'الاسم',
            'البريد الإلكتروني',
            'المدرسة',
            'الهاتف',
            'الفصول الدراسية',
            'عدد الطلاب',
            'الحالة',
            'تاريخ التسجيل',
        ];
    }

    public function map($teacher): array
    {
        $classrooms = $teacher->teachingClassrooms;
        $studentsCount = $classrooms->sum(function($classroom) {
            return $classroom->students()->count();
        });

        return $this->sanitizeRow([
            $teacher->id,
            $teacher->name,
            $teacher->email,
            $teacher->school->name ?? 'غير محدد',
            $teacher->phone ?? '-',
            $classrooms->pluck('name')->implode(', ') ?: 'لا يوجد',
            $studentsCount,
            $teacher->status === 'active' ? 'نشط' : 'غير نشط',
            $teacher->created_at->format('Y-m-d'),
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        // تنسيق العنوان
        $sheet->getStyle('A1:I1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '667eea'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // جعل العنوان ثابت
        $sheet->freezePane('A2');

        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
