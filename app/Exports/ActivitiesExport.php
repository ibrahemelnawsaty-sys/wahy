<?php

namespace App\Exports;

use App\Exports\Concerns\SanitizesCsvOutput;
use App\Models\Activity;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ActivitiesExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    use SanitizesCsvOutput;

    protected $schoolId;

    public function __construct($schoolId = null)
    {
        $this->schoolId = $schoolId;
    }

    public function collection()
    {
        $query = Activity::with(['lesson.concept.value', 'creator', 'submissions']);

        if ($this->schoolId) {
            $query->whereHas('creator', function ($q) {
                $q->where('school_id', $this->schoolId);
            });
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'العنوان',
            'النوع',
            'المستوى',
            'القيمة',
            'المعلم',
            'النقاط',
            'العملات',
            'عدد التقديمات',
            'المكتملة',
            'قيد المراجعة',
            'الحالة',
            'تاريخ الإنشاء',
        ];
    }

    public function map($activity): array
    {
        $types = [
            'homework' => 'واجب منزلي',
            'quiz' => 'اختبار',
            'project' => 'مشروع',
            'assignment' => 'تكليف',
            'practice' => 'تمرين',
        ];

        $difficulties = [
            'easy' => 'سهل',
            'medium' => 'متوسط',
            'hard' => 'صعب',
        ];

        return $this->sanitizeRow([
            $activity->id,
            $activity->title,
            $types[$activity->type] ?? $activity->type,
            $difficulties[$activity->difficulty] ?? $activity->difficulty,
            optional(optional(optional($activity->lesson)->concept)->value)->name ?? 'غير محدد',
            $activity->creator->name ?? 'غير محدد',
            $activity->points ?? 0,
            $activity->coins ?? 0,
            $activity->submissions->count(),
            $activity->submissions->whereIn('status', \App\Models\ActivitySubmission::DONE_STATUSES)->count(),
            $activity->submissions->where('status', 'pending')->count(),
            $activity->status === 'active' ? 'نشط' : 'غير نشط',
            $activity->created_at->format('Y-m-d'),
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
