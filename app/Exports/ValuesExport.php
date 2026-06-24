<?php

namespace App\Exports;

use App\Exports\Concerns\SanitizesCsvOutput;
use App\Models\Value;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ValuesExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    use SanitizesCsvOutput;

    public function collection()
    {
        return Value::withCount([
                'concepts',
                'concepts as total_lessons' => function($q) {
                    $q->join('lessons', 'concepts.id', '=', 'lessons.concept_id');
                },
                'concepts as total_activities' => function($q) {
                    $q->join('lessons', 'concepts.id', '=', 'lessons.concept_id')
                        ->join('activities', 'lessons.id', '=', 'activities.lesson_id');
                },
            ])
            ->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'اسم القيمة',
            'الأيقونة',
            'عدد المفاهيم',
            'عدد الدروس',
            'عدد الأنشطة',
            'الحالة',
            'تاريخ الإنشاء',
        ];
    }

    public function map($value): array
    {
        return $this->sanitizeRow([
            $value->id,
            $value->name,
            $value->icon ?? '-',
            $value->concepts_count,
            $value->total_lessons,
            $value->total_activities,
            $value->status === 'active' ? 'نشط' : 'غير نشط',
            $value->created_at ? $value->created_at->format('Y-m-d') : '-',
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '8b5cf6'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->freezePane('A2');

        foreach(range('A','H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
