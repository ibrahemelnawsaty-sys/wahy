<?php

namespace App\Http\Controllers;

use App\Models\School;
use Illuminate\Support\Facades\Auth;

class SchoolSwitchController extends Controller
{
    /**
     * تبديل المدرسة النشطة لمدير المدرسة (جلسة فقط — لا يُلمس عمود school_id المحروس).
     */
    public function switch(School $school)
    {
        $user = Auth::user();

        // منع الوصول لمدرسة غير مملوكة (تصعيد صلاحيات)
        if (! in_array((int) $school->id, $user->managedSchoolIds(), true)) {
            abort(403, 'هذه المدرسة غير متاحة لك');
        }

        if (! $user->switchSchool((int) $school->id)) {
            abort(403, 'هذه المدرسة غير متاحة لك');
        }

        return redirect()
            ->route('school-admin.dashboard')
            ->with('success', 'تم التبديل إلى مدرسة ' . $school->name);
    }
}
