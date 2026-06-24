<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

/**
 * @group Authentication
 *
 * مسارات تسجيل الدخول والخروج وإدارة الحساب للتطبيق المحمول.
 */
class AuthApiController extends Controller
{
    /**
     * تسجيل الدخول (Login).
     *
     * يرجع Bearer Token عند نجاح المصادقة. استخدم التوكن في Header
     * `Authorization: Bearer {token}` لكل الطلبات المحمية بعدها.
     *
     * @unauthenticated
     *
     * @bodyParam email string required البريد الإلكتروني. Example: student@example.com
     * @bodyParam password string required كلمة المرور (6+ حروف). Example: password123
     *
     * @response 200 {
     *   "success": true,
     *   "message": "تم تسجيل الدخول بنجاح",
     *   "data": {
     *     "token": "1|abcdef1234567890",
     *     "user": {
     *       "id": 1,
     *       "name": "أحمد محمد",
     *       "email": "student@example.com",
     *       "role": "student",
     *       "avatar": null,
     *       "school_id": 5
     *     }
     *   }
     * }
     *
     * @response 401 {
     *   "success": false,
     *   "message": "البريد الإلكتروني أو كلمة المرور غير صحيحة"
     * }
     *
     * @response 403 {
     *   "success": false,
     *   "message": "حسابك غير مفعل. يرجى التواصل مع الإدارة"
     * }
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'البريد الإلكتروني أو كلمة المرور غير صحيحة'
            ], 401);
        }

        // Check if account is active
        if ($user->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'حسابك غير مفعل. يرجى التواصل مع الإدارة'
            ], 403);
        }

        // Create token
        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الدخول بنجاح',
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'avatar' => $user->avatar,
                    'school_id' => $user->school_id,
                ]
            ]
        ], 200);
    }

    /**
     * تسجيل الخروج (Logout).
     *
     * يحذف الـ token الحالي فقط (لا يؤثر على tokens أخرى للمستخدم).
     *
     * @authenticated
     * @response 200 {"success": true, "message": "تم تسجيل الخروج بنجاح"}
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الخروج بنجاح'
        ], 200);
    }

    /**
     * Get User Profile
     */
    public function profile(Request $request)
    {
        $user = $request->user()->load(['school', 'classrooms']);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'avatar' => $user->avatar,
                'phone' => $user->phone,
                'birth_date' => $user->birth_date,
                'school' => $user->school ? [
                    'id' => $user->school->id,
                    'name' => $user->school->name,
                    'logo' => $user->school->logo,
                ] : null,
                'classrooms' => $user->classrooms->map(function($classroom) {
                    return [
                        'id' => $classroom->id,
                        'name' => $classroom->name,
                    ];
                }),
            ]
        ], 200);
    }

    /**
     * Update Profile
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20',
            'avatar' => 'sometimes|image|max:2048',
        ]);

        if ($request->has('name')) {
            $user->name = $request->name;
        }

        if ($request->has('phone')) {
            $user->phone = $request->phone;
        }

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث الملف الشخصي بنجاح',
            'data' => [
                'name' => $user->name,
                'phone' => $user->phone,
                'avatar' => $user->avatar,
            ]
        ], 200);
    }

    /**
     * Change Password
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'كلمة المرور الحالية غير صحيحة'
            ], 400);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'تم تغيير كلمة المرور بنجاح'
        ], 200);
    }
}

