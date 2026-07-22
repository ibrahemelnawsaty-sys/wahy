<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ميزة #23: نشاط «يتطلب اطلاع وموافقة ولي الأمر».
     * - activities.requires_parent_approval: عند تفعيله، تسليم الطالب لا ينتقل لطابور المعلّم
     *   إلا بعد موافقة وليّ الأمر (يأخذ الوليّ نقاطاً على موافقته).
     * - activity_submissions.parent_approval_status: null = غير مطلوب؛ pending = بانتظار الوليّ؛
     *   approved = وافق الوليّ (فيدخل طابور المعلّم).
     */
    public function up(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->boolean('requires_parent_approval')->default(false)->after('manual_review');
        });

        Schema::table('activity_submissions', function (Blueprint $table) {
            $table->string('parent_approval_status')->nullable()->after('status');
            $table->unsignedBigInteger('parent_approved_by')->nullable()->after('parent_approval_status');
            $table->timestamp('parent_approved_at')->nullable()->after('parent_approved_by');
            $table->index('parent_approval_status');
        });
    }

    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn('requires_parent_approval');
        });
        Schema::table('activity_submissions', function (Blueprint $table) {
            $table->dropIndex(['parent_approval_status']);
            $table->dropColumn(['parent_approval_status', 'parent_approved_by', 'parent_approved_at']);
        });
    }
};
