<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\School;
use App\Models\User;
use Illuminate\Validation\Rule;

class SchoolManagementController extends Controller
{
    /**
     * عرض قائمة المدارس
     */
    public function index(Request $request)
    {
        $query = School::with('admin');
        
        // فلترة حسب الحالة
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // فلترة حسب المدينة
        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }
        
        // بحث
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('contact_email', 'like', "%{$search}%")
                  ->orWhere('qr_code', 'like', "%{$search}%");
            });
        }
        
        $schools = $query->latest()->paginate(20);
        
        return view('admin.schools.index', compact('schools'));
    }

    /**
     * عرض صفحة إضافة مدرسة جديدة
     */
    public function create()
    {
        return view('admin.schools.create');
    }

    /**
     * حفظ مدرسة جديدة
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'contact_email' => 'required|email|unique:schools,contact_email',
            'contact_phone' => 'required|string|max:20',
            'qr_code' => 'nullable|string|unique:schools,qr_code',
            'status' => 'required|in:active,inactive',
        ]);

        // توليد QR Code تلقائي
        if (!$request->filled('qr_code')) {
            $validated['qr_code'] = $this->generateSchoolQRCode();
        }

        $validated['created_by'] = auth()->id();
        
        $school = School::create($validated);

        return redirect()
            ->route('admin.schools.index')
            ->with('success', 'تم إضافة المدرسة بنجاح! 🏫');
    }

    /**
     * عرض تفاصيل المدرسة
     */
    public function show(School $school)
    {
        $school->load(['users', 'branches']);
        
        $stats = [
            'total_users' => $school->users()->count(),
            'teachers' => $school->users()->where('role', 'teacher')->count(),
            'students' => $school->users()->where('role', 'student')->count(),
            'parents' => $school->users()->where('role', 'parent')->count(),
            'branches' => $school->branches()->count(),
        ];
        
        return view('admin.schools.show', compact('school', 'stats'));
    }

    /**
     * عرض صفحة تعديل المدرسة
     */
    public function edit(School $school)
    {
        return view('admin.schools.edit', compact('school'));
    }

    /**
     * تحديث بيانات المدرسة
     */
    public function update(Request $request, School $school)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'contact_email' => ['required', 'email', Rule::unique('schools')->ignore($school->id)],
            'contact_phone' => 'required|string|max:20',
            'qr_code' => ['nullable', 'string', Rule::unique('schools')->ignore($school->id)],
            'status' => 'required|in:active,inactive',
        ]);

        $school->update($validated);

        return redirect()
            ->route('admin.schools.index')
            ->with('success', 'تم تحديث بيانات المدرسة بنجاح! ✅');
    }

    /**
     * حذف مدرسة
     */
    public function destroy(School $school)
    {
        // التحقق من وجود مستخدمين مرتبطين
        if ($school->users()->count() > 0) {
            return redirect()
                ->route('admin.schools.index')
                ->with('error', 'لا يمكن حذف المدرسة لوجود مستخدمين مرتبطين بها! ❌');
        }

        $school->delete();

        return redirect()
            ->route('admin.schools.index')
            ->with('success', 'تم حذف المدرسة بنجاح! 🗑️');
    }

    /**
     * تغيير حالة المدرسة
     */
    public function toggleStatus(School $school)
    {
        $newStatus = $school->status === 'active' ? 'inactive' : 'active';
        $school->update(['status' => $newStatus]);

        return back()->with('success', 'تم تغيير حالة المدرسة! ✅');
    }

    /**
     * توليد QR Code فريد للمدرسة
     */
    private function generateSchoolQRCode()
    {
        do {
            $qrCode = 'SCH-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (School::where('qr_code', $qrCode)->exists());

        return $qrCode;
    }

    /**
     * عرض شاشة تفعيل القيم للمدرسة (Issue 11 / 105).
     */
    public function activeValues(School $school)
    {
        $allValues = \App\Models\Value::where('status', 'active')->orderBy('order')->get();
        $activeIds = $school->activeValues()->pluck('values.id')->toArray();

        return view('admin.schools.active-values', compact('school', 'allValues', 'activeIds'));
    }

    /**
     * تحديث القيم المفعّلة للمدرسة.
     * - مصفوفة فارغة → الرجوع للسلوك الافتراضي (كل القيم النشطة).
     */
    public function updateActiveValues(Request $request, School $school)
    {
        $validated = $request->validate([
            'value_ids'   => 'array',
            'value_ids.*' => 'integer|exists:values,id',
        ]);

        $valueIds = $validated['value_ids'] ?? [];

        $payload = [];
        foreach ($valueIds as $vid) {
            $payload[$vid] = [
                'activated_by' => auth()->id(),
                'activated_at' => now(),
            ];
        }

        $school->activeValues()->sync($payload);

        return redirect()
            ->route('admin.schools.active-values', $school)
            ->with('success', count($valueIds) > 0
                ? 'تم تحديث القيم المفعّلة للمدرسة (' . count($valueIds) . ' قيمة)'
                : 'تم إعادة المدرسة إلى السلوك الافتراضي (كل القيم النشطة).');
    }
}

