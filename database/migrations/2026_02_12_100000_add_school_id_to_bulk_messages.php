<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bulk_messages', function (Blueprint $table) {
            $table->unsignedBigInteger('school_id')->nullable()->after('recipient_id');
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('set null');
            $table->index('school_id');
        });

        // تغيير recipient_type من enum إلى string — portable عبر MySQL/SQLite
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE bulk_messages MODIFY COLUMN recipient_type VARCHAR(50) NOT NULL DEFAULT 'all'");
        } else {
            // SQLite — استخدم Schema builder
            Schema::table('bulk_messages', function (Blueprint $table) {
                $table->string('recipient_type', 50)->default('all')->change();
            });
        }
    }

    public function down(): void
    {
        Schema::table('bulk_messages', function (Blueprint $table) {
            $table->dropForeign(['school_id']);
            $table->dropIndex(['school_id']);
            $table->dropColumn('school_id');
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE bulk_messages MODIFY COLUMN recipient_type ENUM('teacher', 'parent', 'student', 'all') NOT NULL");
        }
    }
};
