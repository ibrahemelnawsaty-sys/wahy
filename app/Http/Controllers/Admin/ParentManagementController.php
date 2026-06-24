<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\School;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ParentManagementController extends Controller
{
    /**
     * عرض قائمة أولياء الأمور
     */
    public function index(Request $request)
    {
        $query = User::where('role', 'parent')->with('school');
        
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
                  ->orWhere('qr_code', 'like', "%{$search}%");
            });
        }
        
        $parents = $query->latest()->paginate(20);
        $schools = School::where('status', 'active')->get();
        
        return view('admin.parents.index', compact('parents', 'schools'));
    }

    /**
     * عرض صفحة إضافة ولي أمر
     */
    public function create()
    {
        $schools = School::where('status', 'active')->get();
        return view('admin.parents.create', compact('schools'));
    }

    /**
     * حفظ ولي أمر جديد
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
        ]);

        // دائماً ولي أمر
        $validated['role'] = 'parent';

        // توليد QR Code
        if (!$request->filled('qr_code')) {
            $validated['qr_code'] = $this->generateQRCode();
        }

        $validated['password'] = Hash::make($validated['password']);
        
        User::create($validated);

        return redirect()
            ->route('admin.parents.index')
            ->with('success', 'تم إضافة ولي الأمر بنجاح! ✅');
    }

    /**
     * عرض صفحة تعديل ولي أمر
     */
    public function edit(User $parent)
    {
        // تأكد أنه ولي أمر
        if ($parent->role !== 'parent') {
            abort(404);
        }

        $schools = School::where('status', 'active')->get();
        return view('admin.parents.edit', compact('parent', 'schools'));
    }

    /**
     * تحديث بيانات ولي الأمر
     */
    public function update(Request $request, User $parent)
    {
        // تأكد أنه ولي أمر
        if ($parent->role !== 'parent') {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($parent->id)],
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

        $parent->update($validated);

        return redirect()
            ->route('admin.parents.index')
            ->with('success', 'تم تحديث بيانات ولي الأمر بنجاح! ✅');
    }

    /**
     * حذف ولي أمر
     */
    public function destroy(User $parent)
    {
        // تأكد أنه ولي أمر
        if ($parent->role !== 'parent') {
            abort(404);
        }

        $parent->delete();

        return redirect()
            ->route('admin.parents.index')
            ->with('success', 'تم حذف ولي الأمر بنجاح! 🗑️');
    }

    /**
     * تغيير حالة ولي الأمر
     */
    public function toggleStatus(User $parent)
    {
        if ($parent->role !== 'parent') {
            abort(404);
        }

        $newStatus = $parent->status === 'active' ? 'inactive' : 'active';
        $parent->update(['status' => $newStatus]);

        return back()->with('success', 'تم تغيير حالة ولي الأمر! ✅');
    }

    /**
     * توليد QR Code لولي الأمر
     */
    private function generateQRCode()
    {
        do {
            $qrCode = 'SA-PAR-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (User::where('qr_code', $qrCode)->exists());

        return $qrCode;
    }
}

