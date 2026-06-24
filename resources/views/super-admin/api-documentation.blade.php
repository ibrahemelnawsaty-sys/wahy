@extends('layouts.admin')

@section('title', 'توثيق API')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css">
<style>
    .endpoint { @apply bg-gray-50 border rounded-lg p-4 mb-4; }
    .method { @apply inline-block px-3 py-1 rounded text-white font-bold text-sm; }
    .method-post { @apply bg-green-600; }
    .method-get { @apply bg-blue-600; }
    .method-put { @apply bg-orange-600; }
    .method-delete { @apply bg-red-600; }
</style>
@endpush

@section('content')
<div class="admin-content-header">
    <h1 class="admin-content-title">
        <i class="fas fa-code ml-2"></i>
        توثيق API للموبايل
    </h1>
</div>

<div class="admin-content-body">
            
            <!-- Introduction -->
            <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-xl shadow-lg p-8 mb-8 text-white">
                <h2 class="text-3xl font-bold mb-4">
                    <i class="fas fa-mobile-alt ml-2"></i>
                    RESTful API v1.0
                </h2>
                <p class="text-lg mb-4">API متكامل لبناء تطبيقات الموبايل (iOS/Android) باستخدام Laravel Sanctum</p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
                    <div class="bg-white/10 rounded-lg p-4">
                        <div class="text-sm opacity-90">Base URL</div>
                        <div class="font-mono text-lg">{{ url('/api/v1') }}</div>
                    </div>
                    <div class="bg-white/10 rounded-lg p-4">
                        <div class="text-sm opacity-90">Authentication</div>
                        <div class="font-mono text-lg">Bearer Token</div>
                    </div>
                    <div class="bg-white/10 rounded-lg p-4">
                        <div class="text-sm opacity-90">Response Format</div>
                        <div class="font-mono text-lg">JSON</div>
                    </div>
                </div>
            </div>

            <!-- Table of Contents -->
            <div class="bg-white rounded-xl shadow-sm border p-6 mb-8">
                <h3 class="text-xl font-bold mb-4 flex items-center gap-2">
                    <i class="fas fa-list text-blue-600"></i>
                    المحتويات
                </h3>
                <ul class="space-y-2">
                    <li><a href="#authentication" class="text-blue-600 hover:underline">1. المصادقة (Authentication)</a></li>
                    <li><a href="#student-endpoints" class="text-blue-600 hover:underline">2. نقاط نهاية الطالب (Student Endpoints)</a></li>
                    <li><a href="#error-handling" class="text-blue-600 hover:underline">3. معالجة الأخطاء (Error Handling)</a></li>
                </ul>
            </div>

            <!-- Authentication Section -->
            <div id="authentication" class="bg-white rounded-xl shadow-sm border p-6 mb-8">
                <h3 class="text-2xl font-bold mb-6 flex items-center gap-2">
                    <i class="fas fa-key text-yellow-600"></i>
                    1. المصادقة (Authentication)
                </h3>

                <!-- Login -->
                <div class="endpoint">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="method method-post">POST</span>
                        <code class="text-lg">/api/v1/login</code>
                    </div>
                    <p class="text-gray-600 mb-4">تسجيل الدخول والحصول على Token</p>
                    
                    <div class="mb-4">
                        <h4 class="font-bold mb-2">Request Body:</h4>
                        <pre class="bg-gray-900 text-gray-100 p-4 rounded overflow-x-auto"><code>{
  "email": "student@example.com",
  "password": "password123"
}</code></pre>
                    </div>

                    <div>
                        <h4 class="font-bold mb-2">Response (200):</h4>
                        <pre class="bg-gray-900 text-gray-100 p-4 rounded overflow-x-auto"><code>{
  "success": true,
  "message": "تم تسجيل الدخول بنجاح",
  "data": {
    "token": "1|xxxxxxxxxxxxxxxxxxx",
    "user": {
      "id": 1,
      "name": "أحمد محمد",
      "email": "student@example.com",
      "role": "student",
      "avatar": null,
      "school_id": 1
    }
  }
}</code></pre>
                    </div>
                </div>

                <!-- Logout -->
                <div class="endpoint">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="method method-post">POST</span>
                        <code class="text-lg">/api/v1/logout</code>
                        <span class="text-sm text-orange-600">🔒 يتطلب Token</span>
                    </div>
                    <p class="text-gray-600 mb-4">تسجيل الخروج وإلغاء Token</p>
                    
                    <div class="mb-4">
                        <h4 class="font-bold mb-2">Headers:</h4>
                        <pre class="bg-gray-900 text-gray-100 p-4 rounded overflow-x-auto"><code>Authorization: Bearer {token}</code></pre>
                    </div>

                    <div>
                        <h4 class="font-bold mb-2">Response (200):</h4>
                        <pre class="bg-gray-900 text-gray-100 p-4 rounded overflow-x-auto"><code>{
  "success": true,
  "message": "تم تسجيل الخروج بنجاح"
}</code></pre>
                    </div>
                </div>

                <!-- Get Profile -->
                <div class="endpoint">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="method method-get">GET</span>
                        <code class="text-lg">/api/v1/profile</code>
                        <span class="text-sm text-orange-600">🔒 يتطلب Token</span>
                    </div>
                    <p class="text-gray-600 mb-4">الحصول على بيانات المستخدم الحالي</p>
                    
                    <div>
                        <h4 class="font-bold mb-2">Response (200):</h4>
                        <pre class="bg-gray-900 text-gray-100 p-4 rounded overflow-x-auto"><code>{
  "success": true,
  "data": {
    "id": 1,
    "name": "أحمد محمد",
    "email": "student@example.com",
    "role": "student",
    "avatar": "/storage/avatars/avatar.jpg",
    "phone": "0501234567",
    "birth_date": "2010-05-15",
    "school": {
      "id": 1,
      "name": "مدرسة النور",
      "logo": "/storage/schools/logo.jpg"
    },
    "classrooms": [
      {"id": 1, "name": "الصف الأول أ"}
    ]
  }
}</code></pre>
                    </div>
                </div>

                <!-- Change Password -->
                <div class="endpoint">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="method method-post">POST</span>
                        <code class="text-lg">/api/v1/change-password</code>
                        <span class="text-sm text-orange-600">🔒 يتطلب Token</span>
                    </div>
                    <p class="text-gray-600 mb-4">تغيير كلمة المرور</p>
                    
                    <div class="mb-4">
                        <h4 class="font-bold mb-2">Request Body:</h4>
                        <pre class="bg-gray-900 text-gray-100 p-4 rounded overflow-x-auto"><code>{
  "current_password": "old_password",
  "new_password": "new_password123",
  "new_password_confirmation": "new_password123"
}</code></pre>
                    </div>
                </div>
            </div>

            <!-- Student Endpoints Section -->
            <div id="student-endpoints" class="bg-white rounded-xl shadow-sm border p-6 mb-8">
                <h3 class="text-2xl font-bold mb-6 flex items-center gap-2">
                    <i class="fas fa-graduation-cap text-blue-600"></i>
                    2. نقاط نهاية الطالب (Student Endpoints)
                </h3>

                <!-- Dashboard -->
                <div class="endpoint">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="method method-get">GET</span>
                        <code class="text-lg">/api/v1/student/dashboard</code>
                        <span class="text-sm text-orange-600">🔒 Student Only</span>
                    </div>
                    <p class="text-gray-600 mb-4">إحصائيات لوحة التحكم</p>
                    
                    <div>
                        <h4 class="font-bold mb-2">Response (200):</h4>
                        <pre class="bg-gray-900 text-gray-100 p-4 rounded overflow-x-auto"><code>{
  "success": true,
  "data": {
    "stats": {
      "total_points": 450,
      "total_coins": 120,
      "badges_count": 5,
      "completed_activities": 12,
      "pending_activities": 3,
      "current_streak": 7
    },
    "recent_activities": [...]
  }
}</code></pre>
                    </div>
                </div>

                <!-- Get Activities -->
                <div class="endpoint">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="method method-get">GET</span>
                        <code class="text-lg">/api/v1/student/activities</code>
                        <span class="text-sm text-orange-600">🔒 Student Only</span>
                    </div>
                    <p class="text-gray-600 mb-4">قائمة الأنشطة المتاحة</p>
                    
                    <div class="mb-4">
                        <h4 class="font-bold mb-2">Query Parameters (Optional):</h4>
                        <ul class="list-disc list-inside text-sm space-y-1 text-gray-600">
                            <li><code>type</code> - نوع النشاط (homework, quiz, project)</li>
                            <li><code>difficulty</code> - المستوى (easy, medium, hard)</li>
                            <li><code>page</code> - رقم الصفحة</li>
                        </ul>
                    </div>

                    <div>
                        <h4 class="font-bold mb-2">Response (200):</h4>
                        <pre class="bg-gray-900 text-gray-100 p-4 rounded overflow-x-auto"><code>{
  "success": true,
  "data": {
    "activities": [...],
    "pagination": {
      "total": 45,
      "per_page": 20,
      "current_page": 1,
      "last_page": 3
    }
  }
}</code></pre>
                    </div>
                </div>

                <!-- Submit Activity -->
                <div class="endpoint">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="method method-post">POST</span>
                        <code class="text-lg">/api/v1/student/activities/{id}/submit</code>
                        <span class="text-sm text-orange-600">🔒 Student Only</span>
                    </div>
                    <p class="text-gray-600 mb-4">تقديم إجابة نشاط</p>
                    
                    <div class="mb-4">
                        <h4 class="font-bold mb-2">Request Body (multipart/form-data):</h4>
                        <pre class="bg-gray-900 text-gray-100 p-4 rounded overflow-x-auto"><code>{
  "answers": {
    "q1": "الإجابة الأولى",
    "q2": "الإجابة الثانية"
  },
  "file": [binary file] // اختياري
}</code></pre>
                    </div>

                    <div>
                        <h4 class="font-bold mb-2">Response (200):</h4>
                        <pre class="bg-gray-900 text-gray-100 p-4 rounded overflow-x-auto"><code>{
  "success": true,
  "message": "تم تقديم النشاط بنجاح",
  "data": {
    "id": 123,
    "status": "pending"
  }
}</code></pre>
                    </div>
                </div>

                <!-- Leaderboard -->
                <div class="endpoint">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="method method-get">GET</span>
                        <code class="text-lg">/api/v1/student/leaderboard</code>
                        <span class="text-sm text-orange-600">🔒 Student Only</span>
                    </div>
                    <p class="text-gray-600 mb-4">لوحة المتصدرين (Top 50)</p>
                    
                    <div>
                        <h4 class="font-bold mb-2">Response (200):</h4>
                        <pre class="bg-gray-900 text-gray-100 p-4 rounded overflow-x-auto"><code>{
  "success": true,
  "data": {
    "leaderboard": [
      {
        "rank": 1,
        "id": 5,
        "name": "محمد أحمد",
        "avatar": "/storage/avatars/5.jpg",
        "points": 850
      }
    ],
    "user_rank": 12,
    "user_points": 450
  }
}</code></pre>
                    </div>
                </div>
            </div>

            <!-- Error Handling Section -->
            <div id="error-handling" class="bg-white rounded-xl shadow-sm border p-6 mb-8">
                <h3 class="text-2xl font-bold mb-6 flex items-center gap-2">
                    <i class="fas fa-exclamation-triangle text-red-600"></i>
                    3. معالجة الأخطاء
                </h3>

                <div class="space-y-4">
                    <div class="border-r-4 border-red-500 bg-red-50 p-4">
                        <h4 class="font-bold mb-2">401 Unauthorized</h4>
                        <pre class="bg-gray-900 text-gray-100 p-3 rounded text-sm overflow-x-auto"><code>{"success": false, "message": "غير مصرح"}</code></pre>
                    </div>

                    <div class="border-r-4 border-orange-500 bg-orange-50 p-4">
                        <h4 class="font-bold mb-2">403 Forbidden</h4>
                        <pre class="bg-gray-900 text-gray-100 p-3 rounded text-sm overflow-x-auto"><code>{"success": false, "message": "غير مصرح لك بالوصول"}</code></pre>
                    </div>

                    <div class="border-r-4 border-yellow-500 bg-yellow-50 p-4">
                        <h4 class="font-bold mb-2">422 Validation Error</h4>
                        <pre class="bg-gray-900 text-gray-100 p-3 rounded text-sm overflow-x-auto"><code>{
  "message": "The email field is required.",
  "errors": {
    "email": ["The email field is required."]
  }
}</code></pre>
                    </div>

                    <div class="border-r-4 border-gray-500 bg-gray-50 p-4">
                        <h4 class="font-bold mb-2">500 Server Error</h4>
                        <pre class="bg-gray-900 text-gray-100 p-3 rounded text-sm overflow-x-auto"><code>{"success": false, "message": "حدث خطأ في الخادم"}</code></pre>
                    </div>
                </div>
            </div>

            <!-- Testing with Postman -->
            <div class="bg-gradient-to-r from-green-600 to-teal-600 rounded-xl shadow-lg p-8 text-white">
                <h3 class="text-2xl font-bold mb-4 flex items-center gap-2">
                    <i class="fas fa-flask"></i>
                    اختبار API باستخدام Postman
                </h3>
                <ol class="space-y-3 list-decimal list-inside">
                    <li>افتح Postman وأنشئ Request جديد</li>
                    <li>اختر Method (GET/POST) واكتب URL كامل</li>
                    <li>في Headers أضف: <code class="bg-white/20 px-2 py-1 rounded">Authorization: Bearer {token}</code></li>
                    <li>في Body (للـ POST) اختر raw → JSON</li>
                    <li>اضغط Send وشاهد النتيجة</li>
                </ol>
            </div>

        </main>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js"></script>
@endpush
