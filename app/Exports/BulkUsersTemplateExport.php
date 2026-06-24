<?php

namespace App\Exports;

use App\Exports\Concerns\SanitizesCsvOutput;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class BulkUsersTemplateExport implements FromArray, WithHeadings, WithTitle, WithStyles, WithColumnWidths, WithEvents
{
    use SanitizesCsvOutput;

    protected $role;

    public function __construct($role = 'students')
    {
        $this->role = $role;
    }

    public function array(): array
    {
        // إرجاع أمثلة على البيانات
        $examples = [];
        
        if ($this->role === 'students') {
            $examples = [
                [
                    'أحمد محمد العلي',
                    'ahmed@example.com',
                    '12345678',
                    '2008-05-15',
                    'فصل 1 أ',
                ],
                [
                    'فاطمة علي السالم',
                    'fatima@example.com',
                    '12345679',
                    '2009-03-20',
                    'فصل 2 ب',
                ],
            ];
        } elseif ($this->role === 'teachers') {
            $examples = [
                [
                    'محمد أحمد الكندري',
                    'mohammed@example.com',
                    '12345680',
                    'math@example.com',
                ],
            ];
        } elseif ($this->role === 'parents') {
            $examples = [
                [
                    'خالد علي العبدالله',
                    'khalid@example.com',
                    '12345681',
                    'أحمد محمد العلي',
                ],
            ];
        }
        
        return array_map([$this, 'sanitizeRow'], $examples);
    }

    public function headings(): array
    {
        if ($this->role === 'students') {
            return [
                'name',
                'email',
                'phone',
                'birth_date',
                'classroom',
            ];
        } elseif ($this->role === 'teachers') {
            return [
                'name',
                'email',
                'phone',
                'alternative_email',
            ];
        } elseif ($this->role === 'parents') {
            return [
                'name',
                'email',
                'phone',
                'children',
            ];
        }
        
        return [];
    }

    public function title(): string
    {
        $titles = [
            'students' => 'الطلاب',
            'teachers' => 'المعلمين',
            'parents' => 'أولياء الأمور',
        ];
        
        return $titles[$this->role] ?? 'بيانات';
    }

    public function styles(Worksheet $sheet)
    {
        // التنسيق يتم في AfterSheet event فقط
        // هذا الأسلوب فارغ لتجنب التكرار
    }

    public function columnWidths(): array
    {
        if ($this->role === 'students') {
            return [
                'A' => 25, // الاسم
                'B' => 30, // البريد الإلكتروني
                'C' => 15, // الهاتف
                'D' => 20, // تاريخ الميلاد
                'E' => 20, // الفصل
            ];
        } elseif ($this->role === 'teachers') {
            return [
                'A' => 25, // الاسم
                'B' => 30, // البريد الإلكتروني
                'C' => 15, // الهاتف
                'D' => 30, // البريد الإلكتروني البديل
            ];
        } elseif ($this->role === 'parents') {
            return [
                'A' => 25, // الاسم
                'B' => 30, // البريد الإلكتروني
                'C' => 15, // الهاتف
                'D' => 40, // اسم الطالب
            ];
        }
        
        return [];
    }
    
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastColumn = $this->role === 'students' ? 'E' : ($this->role === 'teachers' ? 'D' : 'D');
                
                // إضافة العنوان العربي في الصف الأول
                $arabicHeadings = [];
                if ($this->role === 'students') {
                    $arabicHeadings = ['الاسم', 'البريد الإلكتروني', 'الهاتف', 'تاريخ الميلاد (YYYY-MM-DD)', 'الفصل'];
                } elseif ($this->role === 'teachers') {
                    $arabicHeadings = ['الاسم', 'البريد الإلكتروني', 'الهاتف', 'البريد الإلكتروني البديل'];
                } elseif ($this->role === 'parents') {
                    $arabicHeadings = ['الاسم', 'البريد الإلكتروني', 'الهاتف', 'اسم الطالب (أو أكثر، مفصولة بفاصلة)'];
                }
                
                // إدراج صف جديد في البداية للعناوين العربية
                $sheet->insertNewRowBefore(1, 1);
                
                // كتابة العنوان العربي في الصف 1
                foreach ($arabicHeadings as $index => $heading) {
                    $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($index + 1);
                    $sheet->setCellValue($column . '1', $heading);
                }
                
                // تنسيق العنوان العربي
                $sheet->getStyle('A1:' . $lastColumn . '1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                        'size' => 12,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '667eea'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ]);
                
                // جعل العنوان ثابت
                $sheet->freezePane('A3');
            },
        ];
    }
}
