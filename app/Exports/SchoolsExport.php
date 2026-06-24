<?php

namespace App\Exports;

use App\Models\School;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Illuminate\Support\Facades\DB;

class SchoolsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function collection()
    {
        return School::withCount([
                'users as students_count' => function($q) {
                    $q->where('role', 'student');
                },
                'users as teachers_count' => function($q) {
                    $q->where('role', 'teacher');
                },
                'users as parents_count' => function($q) {
                    $q->where('role', 'parent');
                },
                'users as active_students_count' => function($q) {
                    $q->where('role', 'student')->where('status', 'active');
                },
            ])
            ->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'اسم المدرسة',
            'المدينة',
            'البريد الإلكتروني',
            'الهاتف',
            'عدد الطلاب',
            'الطلاب النشطين',
            'عدد المعلمين',
            'عدد أولياء الأمور',
            'إجمالي نقاط الطلاب',
            'الحالة',
            'تاريخ الإنشاء',
        ];
    }

    public function map($school): array
    {
        $totalPoints = DB::table('points')
            ->join('users', 'points.user_id', '=', 'users.id')
            ->where('users.school_id', $school->id)
            ->where('users.role', 'student')
            ->sum('points.points');

        return [
            $school->id,
            $school->name,
            $school->city ?? '-',
            $school->email ?? '-',
            $school->phone ?? '-',
            $school->students_count,
            $school->active_students_count,
            $school->teachers_count,
            $school->parents_count,
            number_format($totalPoints),
            $school->status === 'active' ? 'نشط' : 'غير نشط',
            $school->created_at->format('Y-m-d'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:L1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '10b981'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->freezePane('A2');

        foreach(range('A','L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
