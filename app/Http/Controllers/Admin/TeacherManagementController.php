<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\School;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class TeacherManagementController extends Controller
{
    /**
     * عرض قائمة المعلمين
     */
    public function index(Request $request)
    {
        $query = User::where('role', 'teacher')->with('school');
        
        // فلترة حسب المدرسة
        if ($request->filled('school_id')) {
            $query->where('school_id', $request->school_id);
        }
        
        // فلترة حسب الحالة
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // بحث
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('qr_code', 'like', "%{$search}%");
            });
        }
        
        $teachers = $query->latest()->paginate(20);
        $schools = School::where('status', 'active')->get();
        
        return view('admin.teachers.index', compact('teachers', 'schools'));
    }

    /**
     * عرض صفحة إضافة معلم
     */
    public function create()
    {
        $schools = School::where('status', 'active')->get();
        return view('admin.teachers.create', compact('schools'));
    }

    /**
     * حفظ معلم جديد
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'school_id' => 'required|exists:schools,id',
            'phone' => 'required|string|max:20',
            'qr_code' => 'nullable|string|unique:users,qr_code',
            'status' => 'required|in:active,inactive,suspended',
        ], [
            'name.required' => 'الاسم مطلوب',
            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.email' => 'يرجى إدخال بريد إلكتروني صحيح',
            'email.unique' => 'البريد الإلكتروني مستخدم بالفعل، يرجى استخدام بريد آخر',
            'password.required' => 'كلمة المرور مطلوبة',
            'password.min' => 'كلمة المرور يجب أن تكون 8 أحرف على الأقل',
            'password.confirmed' => 'تأكيد كلمة المرور غير مطابق',
            'school_id.required' => 'يرجى اختيار المدرسة',
            'school_id.exists' => 'المدرسة المختارة غير موجودة',
            'phone.required' => 'رقم الجوال مطلوب',
            'qr_code.unique' => 'رمز QR مستخدم بالفعل',
        ]);

        try {
            // دائماً معلم
            $validated['role'] = 'teacher';

            // توليد QR Code
            if (!$request->filled('qr_code')) {
                $validated['qr_code'] = $this->generateQRCode();
            }

            $validated['password'] = Hash::make($validated['password']);
            
            User::create($validated);

            return redirect()
                ->route('admin.teachers.index')
                ->with('success', 'تم إضافة المعلم بنجاح! ✅');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Teacher creation failed', ['error' => $e->getMessage()]);
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء إضافة المعلم');
        }
    }

    /**
     * عرض صفحة تعديل معلم
     */
    public function edit(User $teacher)
    {
        // تأكد أنه معلم
        if ($teacher->role !== 'teacher') {
            abort(404);
        }

        $schools = School::where('status', 'active')->get();
        return view('admin.teachers.edit', compact('teacher', 'schools'));
    }

    /**
     * تحديث بيانات المعلم
     */
    public function update(Request $request, User $teacher)
    {
        // تأكد أنه معلم
        if ($teacher->role !== 'teacher') {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($teacher->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'school_id' => 'required|exists:schools,id',
            'phone' => 'required|string|max:20',
            'status' => 'required|in:active,inactive,suspended',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $teacher->update($validated);

        return redirect()
            ->route('admin.teachers.index')
            ->with('success', 'تم تحديث بيانات المعلم بنجاح! ✅');
    }

    /**
     * حذف معلم
     */
    public function destroy(User $teacher)
    {
        // تأكد أنه معلم
        if ($teacher->role !== 'teacher') {
            abort(404);
        }

        $teacher->delete();

        return redirect()
            ->route('admin.teachers.index')
            ->with('success', 'تم حذف المعلم بنجاح! 🗑️');
    }

    /**
     * تغيير حالة المعلم
     */
    public function toggleStatus(User $teacher)
    {
        if ($teacher->role !== 'teacher') {
            abort(404);
        }

        $newStatus = $teacher->status === 'active' ? 'inactive' : 'active';
        $teacher->update(['status' => $newStatus]);

        return back()->with('success', 'تم تغيير حالة المعلم! ✅');
    }

    /**
     * توليد QR Code للمعلم
     */
    private function generateQRCode()
    {
        do {
            $qrCode = 'SA-TCH-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (User::where('qr_code', $qrCode)->exists());

        return $qrCode;
    }
}

