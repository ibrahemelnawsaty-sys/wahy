<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * نوع المدرسة (بنين/بنات): male = بنين، female = بنات. المدارس كلّها إمّا بنين أو بنات، فجنس كلّ طالب
 * يُشتقّ منها تلقائيّاً (User::saving). nullable: المدارس القائمة بلا قيمة حتى يحدّدها الأدمن —
 * سلوكها الحاليّ لا يتغيّر (لا يُخمَّن جنسها).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->string('gender', 10)->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn('gender');
        });
    }
};
