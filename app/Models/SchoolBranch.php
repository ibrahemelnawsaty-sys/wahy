<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolBranch extends Model
{
    protected $fillable = [
        'school_id',
        'branch_name',
        'address',
        'city',
        'contact_phone',
        'manager_name',
    ];

    /**
     * العلاقة مع المدرسة الأم
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * العلاقة مع الفصول الدراسية في نفس مدرسة الفرع.
     * ملاحظة: جدول classrooms حالياً يحوي school_id فقط بدون branch_id،
     * لذا نُرجع الفصول عبر علاقة المدرسة الأم (تُمنع latent bug عند الاستخدام).
     */
    public function classrooms(): HasMany
    {
        return $this->hasMany(Classroom::class, 'school_id', 'school_id');
    }
}
