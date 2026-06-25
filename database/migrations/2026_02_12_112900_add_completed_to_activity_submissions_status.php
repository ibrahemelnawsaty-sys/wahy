<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE activity_submissions MODIFY COLUMN status ENUM('pending', 'approved', 'rejected', 'needs_review', 'completed') DEFAULT 'pending'");
        }
        // SQLite — لا يدعم ENUM، الحقل أصلاً string بدون تطبيق قيود (لا حاجة للتغيير)
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE activity_submissions MODIFY COLUMN status ENUM('pending', 'approved', 'rejected', 'needs_review') DEFAULT 'pending'");
        }
    }
};
