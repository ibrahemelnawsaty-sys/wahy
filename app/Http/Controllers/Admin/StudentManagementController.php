<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class StudentManagementController extends Controller
{
    /**
     * عرض قائمة الطلاب
     */
    public function index(Request $request)
    {
        $query = User::where('role', 'student')->with('school');

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
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('qr_code', 'like', "%{$search}%");
            });
        }

        $students = $query->latest()->paginate(20);
        $schools = School::where('status', 'active')->get();

        return view('admin.students.index', compact('students', 'schools'));
    }

    /**
     * عرض صفحة إضافة طالب
     */
    public function create()
    {
        $schools = School::where('status', 'active')->get();

        return view('admin.students.create', compact('schools'));
    }

    /**
     * حفظ طالب جديد
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'school_id' => 'required|exists:schools,id',
            'qr_code' => 'nullable|string|unique:users,qr_code',
            'status' => 'required|in:active,inactive,suspended',
        ]);

        // دائماً طالب
        $validated['role'] = 'student';

        // توليد QR Code
        if (! $request->filled('qr_code')) {
            $validated['qr_code'] = $this->generateQRCode();
        }

        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()
            ->route('admin.students.index')
            ->with('success', 'تم إضافة الطالب بنجاح! ✅');
    }

    /**
     * عرض صفحة تعديل طالب
     */
    public function edit(User $student)
    {
        // تأكد أنه طالب
        if ($student->role !== 'student') {
            abort(404);
        }

        $schools = School::where('status', 'active')->get();

        return view('admin.students.edit', compact('student', 'schools'));
    }

    /**
     * تحديث بيانات الطالب
     */
    public function update(Request $request, User $student)
    {
        // تأكد أنه طالب
        if ($student->role !== 'student') {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($student->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'school_id' => 'required|exists:schools,id',
            'status' => 'required|in:active,inactive,suspended',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $student->update($validated);

        return redirect()
            ->route('admin.students.index')
            ->with('success', 'تم تحديث بيانات الطالب بنجاح! ✅');
    }

    /**
     * حذف طالب
     */
    public function destroy(User $student)
    {
        // تأكد أنه طالب
        if ($student->role !== 'student') {
            abort(404);
        }

        $student->delete();

        return redirect()
            ->route('admin.students.index')
            ->with('success', 'تم حذف الطالب بنجاح! 🗑️');
    }

    /**
     * تغيير حالة الطالب
     */
    public function toggleStatus(User $student)
    {
        if ($student->role !== 'student') {
            abort(404);
        }

        $newStatus = $student->status === 'active' ? 'inactive' : 'active';
        $student->update(['status' => $newStatus]);

        return back()->with('success', 'تم تغيير حالة الطالب! ✅');
    }

    /**
     * توليد QR Code للطالب
     */
    private function generateQRCode()
    {
        do {
            $qrCode = 'SA-STU-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (User::where('qr_code', $qrCode)->exists());

        return $qrCode;
    }
}
