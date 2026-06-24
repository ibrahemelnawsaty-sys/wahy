<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class StudentsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $schoolId;

    public function __construct($schoolId = null)
    {
        $this->schoolId = $schoolId;
    }

    public function collection()
    {
        $query = User::with(['school', 'classrooms'])
            ->where('role', 'student');

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
            'الفصول',
            'الهاتف',
            'تاريخ الميلاد',
            'الحالة',
            'إجمالي النقاط',
            'إجمالي العملات',
            'عدد الأوسمة',
            'تاريخ التسجيل',
        ];
    }

    public function map($student): array
    {
        return [
            $student->id,
            $student->name,
            $student->email,
            $student->school->name ?? 'غير محدد',
            $student->classrooms->pluck('name')->implode(', ') ?: 'لا يوجد',
            $student->phone ?? '-',
            $student->birth_date ?? '-',
            $student->status === 'active' ? 'نشط' : 'غير نشط',
            $student->points()->sum('points'),
            $student->coins()->sum('coins'),
            $student->badges()->count(),
            $student->created_at->format('Y-m-d'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // تنسيق العنوان
        $sheet->getStyle('A1:L1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '48c6ef'],
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
