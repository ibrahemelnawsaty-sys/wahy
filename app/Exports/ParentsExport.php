<?php

namespace App\Exports;

use App\Exports\Concerns\SanitizesCsvOutput;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ParentsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    use SanitizesCsvOutput;

    protected $schoolId;

    public function __construct($schoolId = null)
    {
        $this->schoolId = $schoolId;
    }

    public function collection()
    {
        $query = User::with(['school', 'children'])
            ->where('role', 'parent');

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
            'الأبناء',
            'عدد الأبناء',
            'الحالة',
            'تاريخ التسجيل',
        ];
    }

    public function map($parent): array
    {
        $children = $parent->children;

        return $this->sanitizeRow([
            $parent->id,
            $parent->name,
            $parent->email,
            $parent->school->name ?? 'غير محدد',
            $parent->phone ?? '-',
            $children->pluck('name')->implode(', ') ?: 'لا يوجد',
            $children->count(),
            $parent->status === 'active' ? 'نشط' : 'غير نشط',
            $parent->created_at->format('Y-m-d'),
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
                'startColor' => ['rgb' => 'a8edea'],
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
