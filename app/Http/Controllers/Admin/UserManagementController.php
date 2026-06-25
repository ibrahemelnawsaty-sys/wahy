<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    /**
     * عرض قائمة جميع المستخدمين
     */
    public function index(Request $request)
    {
        $query = User::with('school');

        // فلترة حسب الدور
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

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

        $users = $query->latest()->paginate(20);
        $schools = School::where('status', 'active')->get();

        return view('admin.users.index', compact('users', 'schools'));
    }

    /**
     * عرض صفحة إضافة مستخدم جديد
     */
    public function create()
    {
        $schools = School::where('status', 'active')->get();

        return view('admin.users.create', compact('schools'));
    }

    /**
     * حفظ مستخدم جديد
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:super_admin,school_admin,teacher,student,parent',
            'school_id' => 'required_if:role,school_admin,teacher,student,parent|nullable|exists:schools,id',
            'phone' => 'nullable|string|max:20',
            'qr_code' => 'nullable|string|unique:users,qr_code',
            'status' => 'required|in:active,inactive,suspended',
        ], [
            'school_id.required_if' => 'يجب تحديد المدرسة لهذا الدور',
        ]);

        // توليد QR Code تلقائي إذا لم يتم إدخاله
        if (! $request->filled('qr_code')) {
            $validated['qr_code'] = $this->generateQRCode($validated['role']);
        }

        // التحقق بخطوتين (checkbox)
        $validated['two_factor_enabled'] = $request->has('two_factor_enabled') ? true : false;

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'تم إضافة المستخدم بنجاح! ✅');
    }

    /**
     * عرض صفحة تعديل مستخدم
     */
    public function edit(User $user)
    {
        $schools = School::where('status', 'active')->get();

        return view('admin.users.edit', compact('user', 'schools'));
    }

    /**
     * تحديث بيانات المستخدم
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|in:super_admin,school_admin,teacher,student,parent',
            'school_id' => 'nullable|exists:schools,id',
            'phone' => 'nullable|string|max:20',
            'qr_code' => ['nullable', 'string', Rule::unique('users')->ignore($user->id)],
            'status' => 'required|in:active,inactive,suspended',
        ]);

        // تحديث كلمة المرور فقط إذا تم إدخالها
        if ($request->filled('password')) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        // التحقق بخطوتين (checkbox)
        $validated['two_factor_enabled'] = $request->has('two_factor_enabled') ? true : false;

        $user->update($validated);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'تم تحديث بيانات المستخدم بنجاح! ✅');
    }

    /**
     * حذف مستخدم
     */
    public function destroy(User $user)
    {
        // منع حذف الحساب الحالي
        if ($user->id === auth()->id()) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', 'لا يمكنك حذف حسابك الخاص! ❌');
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'تم حذف المستخدم بنجاح! 🗑️');
    }

    /**
     * تغيير حالة المستخدم
     */
    public function toggleStatus(User $user)
    {
        $newStatus = $user->status === 'active' ? 'inactive' : 'active';
        $user->update(['status' => $newStatus]);

        return back()->with('success', 'تم تغيير حالة المستخدم! ✅');
    }

    /**
     * توليد QR Code فريد
     */
    private function generateQRCode($role)
    {
        $prefix = match ($role) {
            'super_admin' => 'SA-ADM',
            'school_admin' => 'SA-SCH-ADM',
            'teacher' => 'SA-TCH',
            'student' => 'SA-STU',
            'parent' => 'SA-PAR',
            default => 'SA-USR'
        };

        do {
            $qrCode = $prefix . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (User::where('qr_code', $qrCode)->exists());

        return $qrCode;
    }
}
