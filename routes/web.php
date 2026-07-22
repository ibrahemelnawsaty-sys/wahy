<?php

use App\Http\Controllers\Admin\ActivityManagementController;
use App\Http\Controllers\Admin\BadgeController;
use App\Http\Controllers\Admin\ConceptManagementController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LandingPageController;
use App\Http\Controllers\Admin\LessonManagementController;
use App\Http\Controllers\Admin\PageBuilderController;
use App\Http\Controllers\Admin\ParentManagementController;
use App\Http\Controllers\Admin\ReportsController;
use App\Http\Controllers\Admin\SchoolManagementController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\StudentManagementController;
use App\Http\Controllers\Admin\SurveyController;
use App\Http\Controllers\Admin\TeacherManagementController;
use App\Http\Controllers\Admin\ThemeController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\ValueManagementController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BulkMessageController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\PagesController;
use App\Http\Controllers\ParentController;
use App\Http\Controllers\PublicRegistrationController;
use App\Http\Controllers\SchoolAdminController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\Support\DashboardController as SupportDashboardController;
use App\Http\Controllers\Support\SupportTicketController;
use App\Http\Controllers\Support\SupportUserController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

// Health check endpoints
Route::get('/health', [\App\Http\Controllers\Health\HealthCheckController::class, 'ping'])->name('health.ping');
Route::get('/health/detailed', [\App\Http\Controllers\Health\HealthCheckController::class, 'detailed'])
    ->middleware(['auth', 'role:super_admin'])
    ->name('health.detailed');

// Live updates (Polling) — عدّادات لحظية موحّدة لكل الأدوار (تحديث الشارات دون refresh)
Route::get('/live/summary', [\App\Http\Controllers\LiveUpdatesController::class, 'summary'])
    ->middleware('auth')->name('live.summary');

// الاستبيانات العامة
Route::get('/survey/{survey}', [PagesController::class, 'showSurvey'])->name('survey.show');

Route::post('/survey/{survey}', [\App\Http\Controllers\SurveyController::class, 'submit'])->name('survey.submit')->middleware('throttle:10,1');

// API للاستبيانات الإجبارية (للبوب أب)
Route::middleware('auth')->group(function () {
    Route::post('/survey/{survey}/submit', [\App\Http\Controllers\SurveyController::class, 'submit'])->name('survey.ajax-submit');
    Route::get('/api/pending-surveys', [\App\Http\Controllers\SurveyController::class, 'getPendingSurveys'])->name('survey.pending');
});

// الصفحة الرئيسية العامة
Route::get('/', [PagesController::class, 'landing'])->name('landing');

// Landing Content Management API
Route::get('/api/landing/content', [\App\Http\Controllers\Api\LandingContentController::class, 'index']);

// باقي العمليات محمية (Super Admin Only)
Route::middleware(['auth', 'role:super_admin'])->prefix('api/landing')->group(function () {
    Route::post('/content/update', [\App\Http\Controllers\Api\LandingContentController::class, 'update']);
    Route::post('/content/bulk-update', [\App\Http\Controllers\Api\LandingContentController::class, 'bulkUpdate']);
    Route::post('/content/upload-image', [\App\Http\Controllers\Api\LandingContentController::class, 'uploadImage']);
    Route::post('/content/restore/{versionId}', [\App\Http\Controllers\Api\LandingContentController::class, 'restoreVersion']);
    Route::post('/content/snapshot', [PagesController::class, 'landingSnapshot']);
});

// عرض الصفحات الديناميكية بـ Page Builder
Route::get('/pages/{slug}', [PagesController::class, 'showPage'])->name('pages.show');

// الصفحة الرئيسية المخصصة
Route::get('/home', [PagesController::class, 'home'])->name('home.custom');

// صفحة التسجيل
Route::get('/register', [PagesController::class, 'register'])->name('register');

Route::post('/register', [AuthController::class, 'register'])
    ->name('register.post')
    ->middleware('throttle:5,1');

// نموذج التواصل — مع throttle لمنع spam
Route::post('/contact', [ContactController::class, 'store'])
    ->name('contact.store')
    ->middleware('throttle:5,1');

// مسارات التسجيل العام للمدارس
Route::prefix('register')->name('public.register.')->group(function () {
    Route::get('/teacher/{token}', [PublicRegistrationController::class, 'showTeacherForm'])->name('teacher');
    Route::post('/teacher/{token}', [PublicRegistrationController::class, 'registerTeacher'])->name('teacher.submit')->middleware('throttle:6,1');
    Route::get('/student/{token}', [PublicRegistrationController::class, 'showStudentForm'])->name('student');
    Route::post('/student/{token}', [PublicRegistrationController::class, 'registerStudent'])->name('student.submit')->middleware('throttle:6,1');
    Route::get('/parent/{token}', [PublicRegistrationController::class, 'showParentForm'])->name('parent');
    Route::post('/parent/{token}', [PublicRegistrationController::class, 'registerParent'])->name('parent.submit')->middleware('throttle:6,1');
});

// عرض الصفحات المبنية بـ Page Builder (alt URL)
Route::get('/page/{slug}', [PagesController::class, 'showPageAlt'])->name('page.show');

// CSRF Token Refresh — محمي بالمصادقة فقط
Route::middleware('auth')->get('/refresh-csrf', [PagesController::class, 'refreshCsrf'])->name('refresh.csrf');

// المصادقة (Authentication) - مع Rate Limiting للحماية من Brute Force
Route::middleware(['guest', 'throttle:20,1'])->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    // Password Reset
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email')->middleware('throttle:5,1');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

// Two Factor Authentication - خارج guest middleware للحفاظ على الجلسة
Route::middleware(['throttle:10,1'])->group(function () {
    Route::get('/two-factor/verify', [AuthController::class, 'showTwoFactorVerify'])->name('two-factor.verify');
    Route::post('/two-factor/verify', [AuthController::class, 'verifyTwoFactor'])->name('two-factor.verify.post');
    Route::post('/two-factor/resend', [AuthController::class, 'resendTwoFactorCode'])->name('two-factor.resend')->middleware('throttle:5,1');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');

    // تحديث صورة الملف الشخصي
    Route::post('/profile/update-avatar', [\App\Http\Controllers\ProfileController::class, 'updateAvatar'])->name('profile.update-avatar');

    // رفع صور محرر النصوص (للدروس والأنشطة)
    Route::post('/editor/upload-image', [\App\Http\Controllers\EditorUploadController::class, 'uploadImage'])->name('editor.upload-image');

    // تبديل الأدوار — POST مع حماية CSRF (تم تغييرها من GET لمنع CSRF)
    Route::post('/switch-role/{role}', [\App\Http\Controllers\RoleSwitchController::class, 'switch'])->name('switch.role');
    Route::post('/switch-role-reset', [\App\Http\Controllers\RoleSwitchController::class, 'resetToPrimary'])->name('switch.role.reset');

    // تبديل المدرسة النشطة لمدير المدرسة (جلسة فقط — POST مع حماية CSRF)
    Route::post('/switch-school/{school}', [\App\Http\Controllers\SchoolSwitchController::class, 'switch'])->name('switch.school');

    // ==================== الرسائل الجماعية ====================
    // يجب أن تكون قبل messages/{userId} لتجنب التعارض
    Route::prefix('messages/bulk')->name('messages.bulk.')->group(function () {
        Route::get('/', [BulkMessageController::class, 'index'])->name('index');
        Route::get('/create', [BulkMessageController::class, 'create'])->name('create');
        Route::post('/send', [BulkMessageController::class, 'send'])->name('send');
        Route::get('/inbox', [BulkMessageController::class, 'inbox'])->name('inbox');
        Route::post('/{id}/read', [BulkMessageController::class, 'markAsRead'])->name('read');
        Route::get('/recipient-count', [BulkMessageController::class, 'getRecipientCount'])->name('recipient-count');
    });

    // نظام الرسائل الشامل - متاح لجميع المستخدمين
    Route::prefix('messages')->name('messages.')->group(function () {
        Route::get('/', [\App\Http\Controllers\MessagesController::class, 'index'])->name('index');
        Route::get('/conversation/{userId}', [\App\Http\Controllers\MessagesController::class, 'getConversation'])->name('conversation');
        Route::post('/send', [\App\Http\Controllers\MessagesController::class, 'send'])->name('send');
        Route::get('/unread/count', [\App\Http\Controllers\MessagesController::class, 'unreadCount'])->name('unread.count');
        Route::get('/check-new/{userId}', [\App\Http\Controllers\MessagesController::class, 'checkNewMessages'])->name('check.new');
        Route::get('/check-all/new', [\App\Http\Controllers\MessagesController::class, 'checkAllNewMessages'])->name('check.all');
        Route::post('/upload', [\App\Http\Controllers\MessagesController::class, 'chatUpload'])->name('upload');
        // يجب أن يكون {userId} في النهاية لأنه يلتقط أي شيء
        Route::get('/{userId}', [\App\Http\Controllers\MessagesController::class, 'show'])->name('show');
    });

    // تغيير كلمة المرور المؤقتة
    Route::get('/password/change', [AuthController::class, 'showPasswordChange'])->name('password.change');
    Route::post('/password/change', [AuthController::class, 'updatePassword'])->name('password.change.update');

    // ==================== لوحات الصدارة ====================
    Route::prefix('leaderboard')->name('leaderboard.')->group(function () {
        Route::get('/', [LeaderboardController::class, 'index'])->name('index');
        Route::get('/students', [LeaderboardController::class, 'students'])->name('students');
        Route::get('/teachers', [LeaderboardController::class, 'teachers'])->name('teachers');
        Route::get('/parents', [LeaderboardController::class, 'parents'])->name('parents');
        Route::get('/schools', [LeaderboardController::class, 'schools'])->name('schools');
    });

    // Admin Panel Routes (Super Admin Only)
    // NOTE (Pass-4): force-2fa enforcement intentionally NOT applied (admin-lockout safety on
    // shared hosting). A non-enrolled super_admin must never be redirect-trapped out of their
    // own panel. The Force2FAForAdmins middleware + 'force-2fa' alias remain in place; to
    // RE-ENABLE later (after confirming the self-enroll page works in prod), add 'force-2fa'
    // back to this middleware array. 2FA still works opt-in (a user who enables two_factor_enabled
    // is challenged on web + API login); only the MANDATORY enrollment enforcement is off.
    Route::prefix('admin')->name('admin.')->middleware(['can:access-admin'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // التقديمات المعلقة
        Route::get('/pending-submissions', [DashboardController::class, 'pendingSubmissions'])->name('pending-submissions');
        Route::get('/review-submission/{id}', [DashboardController::class, 'reviewSubmission'])->name('review-submission');
        Route::post('/review-submission/{id}', [DashboardController::class, 'saveReview'])->name('save-review');

        // Theme Customization
        Route::get('/theme', [ThemeController::class, 'index'])->name('theme');
        Route::post('/theme', [ThemeController::class, 'update'])->name('theme.update');
        Route::post('/theme/upload', [ThemeController::class, 'upload'])->name('theme.upload');

        // Reports
        Route::get('/reports', [ReportsController::class, 'index'])->name('reports.index');

        // الأنشطة المميزة
        Route::get('/featured-activities', [SuperAdminController::class, 'featuredActivities'])->name('featured-activities');
        Route::get('/featured-activities/{id}', [SuperAdminController::class, 'showFeaturedActivity'])->name('featured-activities.show');
        Route::post('/featured-activities/{id}/unfeature', [SuperAdminController::class, 'unfeatureActivity'])->name('featured-activities.unfeature');

        // Page Builder
        Route::get('/pages', [PageBuilderController::class, 'index'])->name('pages.index');
        Route::get('/pages/create', [PageBuilderController::class, 'create'])->name('pages.create');
        Route::post('/pages', [PageBuilderController::class, 'store'])->name('pages.store');
        Route::get('/pages/{id}/edit', [PageBuilderController::class, 'edit'])->name('pages.edit');
        Route::put('/pages/{id}', [PageBuilderController::class, 'update'])->name('pages.update');
        Route::delete('/pages/{id}', [PageBuilderController::class, 'destroy'])->name('pages.destroy');
        Route::post('/pages/preview', [PageBuilderController::class, 'preview'])->name('pages.preview');
        Route::get('/pages/preview/show', [PageBuilderController::class, 'showPreview'])->name('pages.preview.show');

        // Landing Page Customization
        Route::get('/landing-page', [LandingPageController::class, 'index'])->name('landing.index');
        Route::post('/landing-page/theme', [LandingPageController::class, 'updateTheme'])->name('landing.theme');
        Route::post('/landing-page/content', [LandingPageController::class, 'updateContent'])->name('landing.content');

        // User Management
        Route::resource('users', UserManagementController::class);
        Route::post('/users/{user}/toggle-status', [UserManagementController::class, 'toggleStatus'])->name('users.toggle-status');

        // School Management
        Route::resource('schools', SchoolManagementController::class);
        Route::post('/schools/{school}/toggle-status', [SchoolManagementController::class, 'toggleStatus'])->name('schools.toggle-status');
        Route::get('/schools/{school}/active-values', [SchoolManagementController::class, 'activeValues'])->name('schools.active-values');
        Route::put('/schools/{school}/active-values', [SchoolManagementController::class, 'updateActiveValues'])->name('schools.active-values.update');

        // Teacher Management
        Route::resource('teachers', TeacherManagementController::class);
        Route::post('/teachers/{teacher}/toggle-status', [TeacherManagementController::class, 'toggleStatus'])->name('teachers.toggle-status');

        // Student Management
        Route::resource('students', StudentManagementController::class);
        Route::post('/students/{student}/toggle-status', [StudentManagementController::class, 'toggleStatus'])->name('students.toggle-status');

        // Parent Management
        Route::resource('parents', ParentManagementController::class);
        Route::post('/parents/{parent}/toggle-status', [ParentManagementController::class, 'toggleStatus'])->name('parents.toggle-status');

        // Value Management (المحتوى التعليمي)
        Route::resource('values', ValueManagementController::class);
        Route::post('/values/{value}/toggle-status', [ValueManagementController::class, 'toggleStatus'])->name('values.toggle-status');

        // Badge Management (إدارة الشارات)
        Route::resource('badges', BadgeController::class);
        Route::post('/badges/{badge}/toggle-status', [BadgeController::class, 'toggleStatus'])->name('badges.toggle-status');

        // Messages Log (سجل الرسائل للأدمن)
        Route::prefix('messages-log')->name('messages-log.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\MessagesLogController::class, 'index'])->name('index');
            Route::get('/statistics', [\App\Http\Controllers\Admin\MessagesLogController::class, 'statistics'])->name('statistics');
            Route::get('/export', [\App\Http\Controllers\Admin\MessagesLogController::class, 'export'])->name('export');
            Route::get('/{id}', [\App\Http\Controllers\Admin\MessagesLogController::class, 'show'])->name('show');
            Route::get('/conversation/{conversationId}', [\App\Http\Controllers\Admin\MessagesLogController::class, 'showConversation'])->name('conversation');
            Route::delete('/{id}', [\App\Http\Controllers\Admin\MessagesLogController::class, 'destroy'])->name('destroy');
        });

        // Concept Management
        Route::resource('concepts', ConceptManagementController::class);

        // Lesson Management
        Route::resource('lessons', LessonManagementController::class);
        Route::post('/lessons/{lesson}/toggle-status', [LessonManagementController::class, 'toggleStatus'])->name('lessons.toggle-status');

        // Activity Management
        Route::resource('activities', ActivityManagementController::class);
        Route::post('/activities/{activity}/toggle-status', [ActivityManagementController::class, 'toggleStatus'])->name('activities.toggle-status');
        Route::post('/activities/upload-image', [ActivityManagementController::class, 'uploadImage'])->name('activities.upload-image');

        // Survey Management (Admin Full Control)
        Route::resource('surveys', SurveyController::class);
        Route::get('/surveys/{survey}/responses', [SurveyController::class, 'responses'])->name('surveys.responses');
        Route::delete('/surveys/{survey}/responses/{userId}', [SurveyController::class, 'deleteResponse'])->name('surveys.responses.delete');
        Route::get('/surveys/{survey}/export', [SurveyController::class, 'export'])->name('surveys.export');
        Route::get('/surveys/{survey}/comparison', [SurveyController::class, 'comparisonReport'])->name('surveys.comparison');
        Route::post('/surveys/{survey}/toggle-status', [\App\Http\Controllers\Admin\SurveyManagementController::class, 'toggleStatus'])->name('surveys.toggle-status');
        Route::get('/surveys/{survey}/export-responses', [\App\Http\Controllers\Admin\SurveyManagementController::class, 'exportResponses'])->name('surveys.export-responses');

        // Reports & Analytics
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/dashboard', [ReportsController::class, 'dashboard'])->name('dashboard');
            Route::get('/students', [ReportsController::class, 'students'])->name('students');
            Route::get('/students/{id}', [ReportsController::class, 'studentDetail'])->name('students.detail');
            Route::get('/schools', [ReportsController::class, 'schools'])->name('schools');
            Route::get('/schools/{id}', [ReportsController::class, 'schoolDetail'])->name('schools.detail');
            Route::get('/activities', [ReportsController::class, 'activities'])->name('activities');
            Route::get('/values', [ReportsController::class, 'values'])->name('values');
            Route::post('/export', [ReportsController::class, 'export'])->name('export');
            Route::post('/export-pdf', [ReportsController::class, 'exportPdf'])->name('export-pdf');
            Route::get('/export-pdf', [ReportsController::class, 'exportPdf'])->name('export-pdf.get');
        });

        // Shop Management
        Route::prefix('shop')->name('shop.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\ShopManagementController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Admin\ShopManagementController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Admin\ShopManagementController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [\App\Http\Controllers\Admin\ShopManagementController::class, 'edit'])->name('edit');
            Route::put('/{id}', [\App\Http\Controllers\Admin\ShopManagementController::class, 'update'])->name('update');
            Route::delete('/{id}', [\App\Http\Controllers\Admin\ShopManagementController::class, 'destroy'])->name('destroy');
        });

        // General Settings
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
        Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');

        // أدوات النظام (System Tools) - Admin = Super Admin
        // النسخ الاحتياطي
        Route::get('/backups', [SuperAdminController::class, 'backups'])->name('backups');
        Route::post('/backups/create', [SuperAdminController::class, 'createBackup'])->name('backups.create');
        Route::get('/backups/download/{filename}', [SuperAdminController::class, 'downloadBackup'])->name('backups.download');
        Route::delete('/backups/delete/{filename}', [SuperAdminController::class, 'deleteBackup'])->name('backups.delete');
        Route::post('/backups/restore', [SuperAdminController::class, 'restoreBackup'])->name('backups.restore');
        Route::post('/backups/cleanup', [SuperAdminController::class, 'cleanupBackups'])->name('backups.cleanup');

        // سجل الأنشطة
        Route::get('/activity-logs', [SuperAdminController::class, 'activityLogs'])->name('activity-logs');
        Route::post('/activity-logs/clean', [SuperAdminController::class, 'cleanActivityLogs'])->name('activity-logs.clean');

        // توثيق API
        Route::get('/api-documentation', [SuperAdminController::class, 'apiDocumentation'])->name('api-documentation');

        // إدارة Excel
        Route::get('/excel-management', [SuperAdminController::class, 'excelManagement'])->name('excel-management');
        Route::get('/export/students', [SuperAdminController::class, 'exportStudents'])->name('export.students');
        Route::get('/export/activities', [SuperAdminController::class, 'exportActivities'])->name('export.activities');
        Route::get('/export/teachers', [SuperAdminController::class, 'exportTeachers'])->name('export.teachers');
        Route::get('/export/parents', [SuperAdminController::class, 'exportParents'])->name('export.parents');
        Route::get('/export/schools', [SuperAdminController::class, 'exportSchools'])->name('export.schools');
        Route::post('/import/students', [SuperAdminController::class, 'importStudents'])->name('import.students');
        Route::get('/download/students-template', [SuperAdminController::class, 'downloadStudentsTemplate'])->name('download.students-template');

        // إدارة بنك الأسئلة (محتفظ بها للتوافق مع الروابط القديمة)
        Route::get('/question-bank', [SuperAdminController::class, 'questionBank'])->name('question-bank.index');
        Route::post('/question-bank/{id}/approve', [SuperAdminController::class, 'approveQuestion'])->name('question-bank.approve');
        Route::post('/question-bank/{id}/reject', [SuperAdminController::class, 'rejectQuestion'])->name('question-bank.reject');
        Route::post('/question-bank/store', [SuperAdminController::class, 'storeQuestion'])->name('question-bank.store');

        // ─── بنك الأنشطة الموحد (الجديد) ───────────────────────────
        Route::get('/activity-bank', [\App\Http\Controllers\Admin\ActivityBankController::class, 'index'])->name('activity-bank.index');
        Route::post('/activity-bank/store', [\App\Http\Controllers\Admin\ActivityBankController::class, 'storeActivity'])->name('activity-bank.store');
        Route::post('/activity-bank/{id}/approve-activity', [\App\Http\Controllers\Admin\ActivityBankController::class, 'approveActivity'])->name('activity-bank.approve-activity');
        Route::post('/activity-bank/{id}/reject-activity', [\App\Http\Controllers\Admin\ActivityBankController::class, 'rejectActivity'])->name('activity-bank.reject-activity');
        Route::post('/activity-bank/{id}/approve-question', [\App\Http\Controllers\Admin\ActivityBankController::class, 'approveQuestion'])->name('activity-bank.approve-question');
        Route::post('/activity-bank/{id}/reject-question', [\App\Http\Controllers\Admin\ActivityBankController::class, 'rejectQuestion'])->name('activity-bank.reject-question');

        // الموافقة على أنشطة بنك الأنشطة
        Route::prefix('activity-approval')->name('activity-approval.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\ActivityApprovalController::class, 'index'])->name('index');
            Route::get('/{activity}', [\App\Http\Controllers\Admin\ActivityApprovalController::class, 'show'])->name('show');
            Route::post('/{activity}/approve', [\App\Http\Controllers\Admin\ActivityApprovalController::class, 'approve'])->name('approve');
            Route::post('/{activity}/reject', [\App\Http\Controllers\Admin\ActivityApprovalController::class, 'reject'])->name('reject');
            Route::post('/bulk-approve', [\App\Http\Controllers\Admin\ActivityApprovalController::class, 'bulkApprove'])->name('bulk-approve');
        });

        // ─── إدارة تحديات PvP (جديد) ──────────────────────────────
        Route::prefix('pvp-challenges')->name('pvp-challenges.')->group(function () {
            Route::get('/', [SuperAdminController::class, 'pvpChallenges'])->name('index');
            Route::get('/create', [SuperAdminController::class, 'createPvpChallenge'])->name('create');
            Route::post('/', [SuperAdminController::class, 'storePvpChallenge'])->name('store');
            Route::get('/{id}/edit', [SuperAdminController::class, 'editPvpChallenge'])->name('edit');
            Route::put('/{id}', [SuperAdminController::class, 'updatePvpChallenge'])->name('update');
            Route::post('/{id}/toggle', [SuperAdminController::class, 'togglePvpChallenge'])->name('toggle');
            Route::delete('/{id}', [SuperAdminController::class, 'destroyPvpChallenge'])->name('destroy');
        });

        // إدارة الصفحة الرئيسية (Landing Page Editor)
        Route::get('/landing-page', [SuperAdminController::class, 'landingPage'])->name('landing-page');
        Route::post('/landing-page/theme', [SuperAdminController::class, 'updateLandingTheme'])->name('landing-page.theme');
        Route::post('/landing-page/content', [SuperAdminController::class, 'updateLandingContent'])->name('landing-page.content');
        Route::post('/landing-page/add-block', [SuperAdminController::class, 'addLandingBlock'])->name('landing-page.add-block');
        Route::put('/landing-page/block/{id}', [SuperAdminController::class, 'updateLandingBlock'])->name('landing-page.update-block');
        Route::delete('/landing-page/block/{id}', [SuperAdminController::class, 'deleteLandingBlock'])->name('landing-page.delete-block');
        Route::post('/landing-page/reorder-blocks', [SuperAdminController::class, 'reorderLandingBlocks'])->name('landing-page.reorder-blocks');
        Route::post('/landing-page/import-current', [SuperAdminController::class, 'importCurrentLanding'])->name('landing-page.import-current');

        // المستخدمين النشطين أون لاين
        Route::get('/online-users', [SuperAdminController::class, 'onlineUsers'])->name('online-users');
        Route::get('/online-users/api', [SuperAdminController::class, 'onlineUsersApi'])->name('online-users.api');

        // المراحل الدراسية والسنوات الدراسية
        Route::get('/education-levels', [SuperAdminController::class, 'educationLevels'])->name('education-levels');
        Route::post('/education-levels/store', [SuperAdminController::class, 'storeLevel'])->name('education-levels.store');
        Route::put('/education-levels/{id}', [SuperAdminController::class, 'updateLevel'])->name('education-levels.update');
        Route::delete('/education-levels/{id}', [SuperAdminController::class, 'deleteLevel'])->name('education-levels.delete');
        Route::post('/academic-years/store', [SuperAdminController::class, 'storeYear'])->name('academic-years.store');
        Route::put('/academic-years/{id}', [SuperAdminController::class, 'updateYear'])->name('academic-years.update');
        Route::delete('/academic-years/{id}', [SuperAdminController::class, 'deleteYear'])->name('academic-years.delete');
        Route::post('/education-levels/link-school', [SuperAdminController::class, 'linkSchoolLevels'])->name('education-levels.link-school');
    });

    // إعادة توجيه الروابط القديمة super-admin إلى admin (للتوافق مع الروابط القديمة)
    Route::prefix('super-admin')->middleware(['role:super_admin'])->group(function () {
        Route::get('/dashboard', fn () => redirect()->route('admin.dashboard'))->name('super-admin.dashboard');
        Route::get('/backups', fn () => redirect()->route('admin.backups'))->name('super-admin.backups');
        Route::get('/activity-logs', fn () => redirect()->route('admin.activity-logs'))->name('super-admin.activity-logs');
        Route::get('/excel-management', fn () => redirect()->route('admin.excel-management'))->name('super-admin.excel-management');
        Route::get('/question-bank', fn () => redirect()->route('admin.question-bank.index'))->name('super-admin.question-bank.index');
        Route::get('/landing-page', fn () => redirect()->route('admin.landing-page'))->name('super-admin.landing-page');
        Route::get('/api-documentation', fn () => redirect()->route('admin.api-documentation'))->name('super-admin.api-documentation');
    });

    // School Admin
    // NOTE (Pass-4 Batch 4): force-2fa intentionally NOT applied here. The only writer of
    // two_factor_enabled is the super_admin-only admin.users.edit form, so a non-enrolled
    // school_admin would be redirected to a 403 dead-end with no way to self-enroll = total
    // lockout. HELD until a school_admin self-service 2FA enrollment route exists. See the
    // super_admin 'admin' group above, which DOES enforce force-2fa (enrollment path exists).
    Route::prefix('school-admin')->name('school-admin.')->middleware(['role:school_admin', 'school.access'])->group(function () {
        Route::get('/dashboard', [SchoolAdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/parent-engagement', [SchoolAdminController::class, 'parentEngagement'])->name('parent-engagement');

        // N12 — مقارنات الاستبيانات القبلية/البعدية
        Route::get('/surveys/comparisons', [SchoolAdminController::class, 'surveyComparisonsList'])->name('surveys.comparisons');
        Route::get('/surveys/{surveyId}/comparison', [SchoolAdminController::class, 'surveyComparison'])->name('surveys.comparison');

        // Test Glass Notifications
        Route::get('/test-notifications', function () {
            return view('test-notifications');
        })->name('test-notifications');

        // روابط التسجيل و QR Codes
        Route::get('/registration-links', [SchoolAdminController::class, 'registrationLinks'])->name('registration-links');
        Route::post('/regenerate-token', [SchoolAdminController::class, 'regenerateToken'])->name('regenerate-token');
        Route::post('/toggle-registration', [SchoolAdminController::class, 'toggleRegistration'])->name('toggle-registration');

        // Teachers Management
        Route::get('/teachers', [SchoolAdminController::class, 'teachers'])->name('teachers');
        Route::get('/teachers/create', [SchoolAdminController::class, 'createTeacher'])->name('teachers.create');
        Route::post('/teachers', [SchoolAdminController::class, 'storeTeacher'])->name('teachers.store');
        Route::get('/teachers/{id}/edit', [SchoolAdminController::class, 'editTeacher'])->name('teachers.edit');
        Route::put('/teachers/{id}', [SchoolAdminController::class, 'updateTeacher'])->name('teachers.update');
        Route::delete('/teachers/{id}', [SchoolAdminController::class, 'deleteTeacher'])->name('teachers.delete');

        // Students Management
        Route::get('/students', [SchoolAdminController::class, 'students'])->name('students');
        Route::get('/students/create', [SchoolAdminController::class, 'createStudent'])->name('students.create');
        Route::post('/students', [SchoolAdminController::class, 'storeStudent'])->name('students.store');
        Route::get('/students/{id}', [SchoolAdminController::class, 'showStudent'])->name('students.show');
        Route::get('/students/{id}/edit', [SchoolAdminController::class, 'editStudent'])->name('students.edit');
        Route::put('/students/{id}', [SchoolAdminController::class, 'updateStudent'])->name('students.update');
        Route::delete('/students/{id}', [SchoolAdminController::class, 'deleteStudent'])->name('students.delete');

        // Parents Management
        Route::get('/parents', [SchoolAdminController::class, 'parents'])->name('parents');
        Route::get('/parents/create', [SchoolAdminController::class, 'createParent'])->name('parents.create');
        Route::post('/parents', [SchoolAdminController::class, 'storeParent'])->name('parents.store');
        Route::get('/parents/{id}/edit', [SchoolAdminController::class, 'editParent'])->name('parents.edit');
        Route::put('/parents/{id}', [SchoolAdminController::class, 'updateParent'])->name('parents.update');
        Route::delete('/parents/{id}', [SchoolAdminController::class, 'deleteParent'])->name('parents.delete');

        // Classrooms Management
        Route::get('/classrooms', [SchoolAdminController::class, 'classrooms'])->name('classrooms');
        Route::get('/classrooms/create', [SchoolAdminController::class, 'createClassroom'])->name('classrooms.create');
        Route::post('/classrooms', [SchoolAdminController::class, 'storeClassroom'])->name('classrooms.store');
        Route::get('/classrooms/{id}/edit', [SchoolAdminController::class, 'editClassroom'])->name('classrooms.edit');
        Route::put('/classrooms/{id}', [SchoolAdminController::class, 'updateClassroom'])->name('classrooms.update');
        Route::delete('/classrooms/{id}', [SchoolAdminController::class, 'deleteClassroom'])->name('classrooms.delete');

        // Registration Requests
        Route::get('/requests', [SchoolAdminController::class, 'registrationRequests'])->name('requests');
        Route::post('/requests/{id}/approve', [SchoolAdminController::class, 'approveRequest'])->name('requests.approve');
        Route::post('/requests/{id}/reject', [SchoolAdminController::class, 'rejectRequest'])->name('requests.reject');

        // Activity Approvals — المرحلة الأولى: اعتماد أنشطة المعلّمين
        Route::get('/activity-approvals', [SchoolAdminController::class, 'activityApprovals'])->name('activity-approvals');
        Route::post('/activity-approvals/{id}/approve', [SchoolAdminController::class, 'approveActivity'])->name('activity-approvals.approve');
        Route::post('/activity-approvals/{id}/reject', [SchoolAdminController::class, 'rejectActivity'])->name('activity-approvals.reject');
        // صفحة تفاصيل النشاط الموحّدة (المرحلة 4)
        Route::get('/activities/{id}', [SchoolAdminController::class, 'showActivity'])->whereNumber('id')->name('activities.show');

        // Excel Import/Export
        Route::get('/excel-management', [SchoolAdminController::class, 'excelManagement'])->name('excel-management');
        Route::get('/download-template', [SchoolAdminController::class, 'downloadTemplate'])->name('download-template');
        Route::post('/import-users', [SchoolAdminController::class, 'importUsers'])->name('import-users');
        Route::get('/export-data', [SchoolAdminController::class, 'exportData'])->name('export-data');

        // Settings - إعدادات المدرسة
        Route::get('/settings', [SchoolAdminController::class, 'settings'])->name('settings');
        Route::post('/settings', [SchoolAdminController::class, 'updateSettings'])->name('settings.update');

        // Statistics & Rankings
        Route::get('/statistics', [SchoolAdminController::class, 'statistics'])->name('statistics');

        // Messages - نظام الرسائل لمدير المدرسة
        Route::get('/messages', [\App\Http\Controllers\MessagesController::class, 'index'])->name('messages.index');
        Route::get('/messages/conversation/{userId}', [\App\Http\Controllers\MessagesController::class, 'getConversation'])->name('messages.conversation');
        Route::get('/messages/{userId}', [\App\Http\Controllers\MessagesController::class, 'show'])->name('messages.show');
        Route::post('/messages/send', [\App\Http\Controllers\MessagesController::class, 'send'])->name('messages.send');
        Route::get('/messages/unread/count', [\App\Http\Controllers\MessagesController::class, 'unreadCount'])->name('messages.unread.count');
        Route::get('/messages/check-new/{userId}', [\App\Http\Controllers\MessagesController::class, 'checkNewMessages'])->name('messages.check.new');
        Route::get('/messages/check-all/new', [\App\Http\Controllers\MessagesController::class, 'checkAllNewMessages'])->name('messages.check.all');
    });

    // Teacher
    Route::prefix('teacher')->name('teacher.')->middleware(['role:teacher', 'school.access'])->group(function () {
        Route::get('/dashboard', [TeacherController::class, 'dashboard'])->name('dashboard');
        Route::get('/parent-engagement', [TeacherController::class, 'parentEngagement'])->name('parent-engagement');

        // N12 — مقارنات الاستبيانات (مفلترة على طلاب فصول المعلم)
        Route::get('/surveys/comparisons', [TeacherController::class, 'surveyComparisonsList'])->name('surveys.comparisons');
        Route::get('/surveys/{surveyId}/comparison', [TeacherController::class, 'surveyComparison'])->name('surveys.comparison');
        Route::get('/review', [TeacherController::class, 'reviewSubmissions'])->name('review');
        Route::get('/review/{id}', [TeacherController::class, 'reviewSubmission'])->name('review.single');
        Route::post('/review/{id}', [TeacherController::class, 'submitReview'])->name('review.submit');
        Route::post('/review/{id}/allow-retry', [TeacherController::class, 'allowRetry'])->name('review.allow-retry');
        Route::get('/students', [TeacherController::class, 'studentReports'])->name('students');
        Route::get('/students/{id}', [TeacherController::class, 'studentDetail'])->name('students.detail');
        Route::get('/classrooms', [TeacherController::class, 'classrooms'])->name('classrooms');
        Route::get('/classrooms/{id}', [TeacherController::class, 'classroomDetail'])->name('classrooms.detail');

        // إدارة الأنشطة
        Route::get('/activities', [TeacherController::class, 'activities'])->name('activities');
        Route::get('/activities/create', [TeacherController::class, 'createActivity'])->name('activities.create');
        Route::post('/activities', [TeacherController::class, 'storeActivity'])->name('activities.store');
        Route::get('/activities/{id}/edit', [TeacherController::class, 'editActivity'])->name('activities.edit');
        Route::put('/activities/{id}', [TeacherController::class, 'updateActivity'])->name('activities.update');
        Route::get('/activities/{id}/preview', [TeacherController::class, 'previewActivity'])->name('activities.preview');
        Route::post('/activities/{id}/resubmit', [TeacherController::class, 'resubmitActivity'])->name('activities.resubmit');
        Route::delete('/activities/{id}', [TeacherController::class, 'deleteActivity'])->name('activities.delete');

        // نظام مكافأة الالتزام للأنشطة
        Route::get('/streak-settings', [TeacherController::class, 'streakSettings'])->name('streak.settings');
        Route::put('/streak-settings', [TeacherController::class, 'updateStreakSettings'])->name('streak.update');

        // بنك الأنشطة والأسئلة
        Route::post('/activity-bank', [TeacherController::class, 'addActivityToBank'])->name('activity-bank.store');
        Route::get('/activity-bank', [TeacherController::class, 'activityBank'])->name('activity-bank.index');
        Route::get('/activity-bank/create', [TeacherController::class, 'createActivity'])->name('activity-bank.create');
        // اختيار من البنك: نسخة قابلة للتعديل / إسناد مرجعيّ بلا نسخ (المرحلة 4ب)
        Route::post('/activity-bank/{id}/clone', [TeacherController::class, 'cloneFromBank'])->whereNumber('id')->name('activity-bank.clone');
        Route::post('/activity-bank/{id}/reference', [TeacherController::class, 'referenceFromBank'])->whereNumber('id')->name('activity-bank.reference');
        // واجهة «بنك الأسئلة» للمعلّم أُزيلت (المرحلة 5) — تُدار الأسئلة من الأدمن؛ بيانات QuestionBank باقية.

        // Teams Management
        Route::get('/teams', [TeacherController::class, 'teams'])->name('teams');
        Route::get('/teams/create', [TeacherController::class, 'createTeam'])->name('teams.create');
        Route::post('/teams', [TeacherController::class, 'storeTeam'])->name('teams.store');
        Route::get('/teams/{id}', [TeacherController::class, 'showTeam'])->name('teams.show');
        Route::get('/teams/{id}/edit', [TeacherController::class, 'editTeam'])->name('teams.edit');
        Route::post('/teams/{id}', [TeacherController::class, 'updateTeam'])->name('teams.update');
        Route::delete('/teams/{id}', [TeacherController::class, 'destroyTeam'])->name('teams.destroy');

        // الأنشطة المميزة
        Route::post('/activities/{id}/feature', [TeacherController::class, 'featureActivity'])->name('activities.feature');
        Route::post('/activities/{id}/unfeature', [TeacherController::class, 'unfeatureActivity'])->name('activities.unfeature');

        // لوحة الصدارة
        Route::get('/leaderboard/teachers', [TeacherController::class, 'teacherLeaderboard'])->name('leaderboard.teachers');
        Route::get('/leaderboard/students', [TeacherController::class, 'studentLeaderboard'])->name('leaderboard.students');

        // إدارة الفرق
        Route::post('/teams/assign-activity', [TeacherController::class, 'assignTeamActivity'])->name('teams.assign');
        Route::post('/teams/activities/{id}/grade', [TeacherController::class, 'gradeTeamActivity'])->name('teams.grade');

        // المراسلات
        Route::get('/messages', [TeacherController::class, 'messages'])->name('messages');
        Route::get('/messages/conversation', [TeacherController::class, 'getConversation'])->name('messages.conversation');
        Route::post('/messages/send', [TeacherController::class, 'sendMessage'])->name('messages.send');

        // التقييمات
        Route::get('/ratings', [TeacherController::class, 'ratings'])->name('ratings');

        // التحليلات والإحصائيات
        Route::get('/analytics', [TeacherController::class, 'analytics'])->name('analytics');

        // تصدير التقارير PDF
        Route::get('/reports/student/{studentId}', [TeacherController::class, 'exportStudentReport'])->name('reports.student');
        Route::get('/reports/classroom/{classroomId}', [TeacherController::class, 'exportClassroomReport'])->name('reports.classroom');

        Route::get('/settings', [TeacherController::class, 'settings'])->name('settings');
        Route::post('/settings/update', [TeacherController::class, 'updateSettings'])->name('settings.update');

        // نظام التمارين
        Route::get('/exercises', [TeacherController::class, 'practiceExercises'])->name('exercises');
        Route::get('/exercises/create', [TeacherController::class, 'createExercise'])->name('exercises.create');
        Route::post('/exercises', [TeacherController::class, 'storeExercise'])->name('exercises.store');
        Route::get('/exercises/{id}/edit', [TeacherController::class, 'editExercise'])->name('exercises.edit');
        Route::put('/exercises/{id}', [TeacherController::class, 'updateExercise'])->name('exercises.update');
        Route::delete('/exercises/{id}', [TeacherController::class, 'deleteExercise'])->name('exercises.delete');
        Route::get('/exercises/{id}/results', [TeacherController::class, 'exerciseResults'])->name('exercises.results');
    });    // Student
    Route::prefix('student')->name('student.')->middleware(['role:student', 'school.access'])->group(function () {
        Route::get('/dashboard', [StudentController::class, 'dashboard'])->name('dashboard');
        Route::get('/path', [StudentController::class, 'learningPath'])->name('path');
        Route::get('/lesson/{id}', [StudentController::class, 'lesson'])->name('lesson');
        Route::get('/practice', [StudentController::class, 'practice'])->name('practice');
        Route::get('/activity/{id}', [StudentController::class, 'activity'])->name('activity');
        Route::post('/activity/{id}/submit', [StudentController::class, 'submitActivity'])->name('activity.submit');
        Route::get('/leaderboard', [StudentController::class, 'leaderboard'])->name('leaderboard');
        Route::get('/profile', [StudentController::class, 'profile'])->name('profile');
        Route::post('/profile/update', [StudentController::class, 'updateProfile'])->name('profile.update');
        Route::get('/coins/history', [StudentController::class, 'coinsHistory'])->name('coins.history');
        Route::get('/shop', [StudentController::class, 'shop'])->name('shop');
        Route::post('/shop/purchase', [StudentController::class, 'purchaseItem'])->name('shop.purchase');
        Route::post('/shop/redeem', [StudentController::class, 'redeemReward'])->name('shop.redeem');
        // مقتنياتي: عرض المشتريات + تجهيز/استخدام العناصر
        Route::get('/my-items', [StudentController::class, 'myItems'])->name('my-items');
        Route::post('/my-items/equip', [StudentController::class, 'equipItem'])->name('my-items.equip');
        Route::post('/my-items/use', [StudentController::class, 'useItem'])->name('my-items.use');
        Route::get('/rate-teachers', [StudentController::class, 'rateTeachers'])->name('rate.teachers');
        Route::post('/rate-teacher', [StudentController::class, 'submitRating'])->name('rate.submit');
        Route::get('/analytics', [StudentController::class, 'analytics'])->name('analytics');

        // الصفحات الجديدة
        Route::get('/badges', [StudentController::class, 'badges'])->name('badges');
        Route::get('/learn', [StudentController::class, 'learn'])->name('learn');
        Route::get('/values-tree', [StudentController::class, 'valuesTree'])->name('values-tree');
        Route::get('/crowns', [StudentController::class, 'crowns'])->name('crowns');
        Route::get('/gifts', [StudentController::class, 'gifts'])->name('gifts');
        Route::get('/teams', [StudentController::class, 'teams'])->name('teams');

        // نظام التمارين
        Route::get('/practice/{id}/start', [StudentController::class, 'startExercise'])->name('practice.start');
        Route::post('/practice/{id}/submit', [StudentController::class, 'submitExercise'])->name('practice.submit');
        Route::get('/practice/result/{attemptId}', [StudentController::class, 'exerciseResult'])->name('practice.result');

        // نظام PvP
        Route::get('/pvp', [StudentController::class, 'pvpLobby'])->name('pvp.lobby');
        Route::post('/pvp/{challengeId}/join', [StudentController::class, 'joinPvpMatch'])->name('pvp.join');
        Route::get('/pvp/{matchId}/status', [StudentController::class, 'pvpMatchStatus'])->name('pvp.status');
        Route::get('/pvp/{matchId}/play', [StudentController::class, 'pvpPlay'])->name('pvp.play');
        Route::post('/pvp/{matchId}/submit', [StudentController::class, 'submitPvpAnswers'])->name('pvp.submit');
        Route::get('/pvp/{matchId}/result', [StudentController::class, 'pvpResult'])->name('pvp.result');
        // تحدٍّ موجّه: اختيار منافس محدّد + قبول/رفض الدعوة
        Route::get('/pvp-opponents/search', [StudentController::class, 'pvpSearchOpponents'])->name('pvp.opponents');
        Route::post('/pvp/{challengeId}/challenge', [StudentController::class, 'challengeOpponent'])->middleware('throttle:20,1')->name('pvp.challenge');
        Route::post('/pvp-invite/{matchId}/accept', [StudentController::class, 'acceptPvpInvite'])->name('pvp.invite.accept');
        Route::post('/pvp-invite/{matchId}/decline', [StudentController::class, 'declinePvpInvite'])->name('pvp.invite.decline');
    });

    // Notifications
    Route::prefix('notifications')->name('notifications.')->middleware('auth')->group(function () {
        Route::get('/', [App\Http\Controllers\NotificationController::class, 'index'])->name('index');
        Route::get('/fetch', [App\Http\Controllers\NotificationController::class, 'fetch'])->name('fetch');
        Route::post('/{id}/read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('read');
        Route::post('/read-all', [App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('read-all');
        Route::delete('/{id}', [App\Http\Controllers\NotificationController::class, 'delete'])->name('delete');
    });    // Parent
    Route::prefix('parent')->name('parent.')->middleware(['role:parent', 'school.access'])->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\ParentDashboardController::class, 'index'])->name('dashboard');
        Route::get('/child/{id}', [App\Http\Controllers\ParentDashboardController::class, 'childDetails'])->name('child.details');

        // N12 — مقارنات الاستبيانات (مفلترة على أبناء ولي الأمر)
        Route::get('/surveys/comparisons', [App\Http\Controllers\ParentDashboardController::class, 'surveyComparisonsList'])->name('surveys.comparisons');
        Route::get('/surveys/{surveyId}/comparison', [App\Http\Controllers\ParentDashboardController::class, 'surveyComparison'])->name('surveys.comparison');
        Route::get('/messages', [ParentController::class, 'messages'])->name('messages');
        Route::get('/messages/conversation', [ParentController::class, 'getConversation'])->name('messages.conversation');
        Route::post('/messages/send', [ParentController::class, 'sendMessage'])->name('messages.send');

        // نظام المدح والهدايا
        Route::post('/children/{id}/praise', [ParentController::class, 'praiseChild'])->name('child.praise');
        Route::post('/children/{id}/gift', [ParentController::class, 'sendGift'])->name('child.gift');

        // الأنشطة العائلية
        Route::get('/family-activities/pending', [ParentController::class, 'pendingFamilyActivities'])->name('family-activities.pending');
        Route::post('/family-activities/{id}/approve', [ParentController::class, 'approveFamilyActivity'])->name('family-activities.approve');
        // ميزة #23: موافقة الوليّ على نشاط عاديّ يتطلّب موافقته (قبل انتقاله للمعلّم)
        Route::post('/family-activities/parent-approval/{id}/approve', [ParentController::class, 'approveParentActivity'])->name('family-activities.parent-approve');
    });

    // ==================== تذاكر الدعم الفنيّ (نهاية المستخدم — كل الأدوار) ====================
    // عابرة للمدارس/الأدوار: بلا role ولا school.access (auth فقط).
    Route::prefix('tickets')->name('tickets.')->group(function () {
        Route::get('/', [TicketController::class, 'index'])->name('index');
        Route::get('/create', [TicketController::class, 'create'])->name('create');
        Route::post('/', [TicketController::class, 'store'])->name('store');
        Route::get('/{ticket}', [TicketController::class, 'show'])->name('show');
        Route::post('/{ticket}/reply', [TicketController::class, 'reply'])->name('reply');
        Route::post('/{ticket}/close', [TicketController::class, 'close'])->name('close');
    });

    // ==================== لوحة الدعم الفنيّ ====================
    // محروسة role:technical_support (والسوبر أدمن يمرّ تلقائياً عبر CheckRole). بلا school.access.
    Route::prefix('support')->name('support.')->middleware(['role:technical_support'])->group(function () {
        Route::get('/dashboard', [SupportDashboardController::class, 'index'])->name('dashboard');

        // إدارة التذاكر
        Route::prefix('tickets')->name('tickets.')->group(function () {
            Route::get('/', [SupportTicketController::class, 'index'])->name('index');
            Route::get('/{ticket}', [SupportTicketController::class, 'show'])->name('show');
            Route::post('/{ticket}/reply', [SupportTicketController::class, 'reply'])->name('reply');
            Route::post('/{ticket}/resolve', [SupportTicketController::class, 'resolve'])->name('resolve');
            Route::post('/{ticket}/reopen', [SupportTicketController::class, 'reopen'])->name('reopen');
            Route::post('/{ticket}/close', [SupportTicketController::class, 'close'])->name('close');
            Route::post('/{ticket}/escalate', [SupportTicketController::class, 'escalate'])->name('escalate');
            Route::post('/{ticket}/assign', [SupportTicketController::class, 'assign'])->name('assign');
        });

        // إدارة المستخدمين (صلاحيات محدودة — لا مساس بالسوبر أدمن)
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [SupportUserController::class, 'index'])->name('index');
            Route::get('/{user}/edit', [SupportUserController::class, 'edit'])->name('edit');
            Route::put('/{user}', [SupportUserController::class, 'update'])->name('update');
            Route::post('/{user}/reset-password', [SupportUserController::class, 'resetPassword'])->name('reset-password');
            Route::post('/{user}/toggle-status', [SupportUserController::class, 'toggleStatus'])->name('toggle-status');
        });
    });
});
