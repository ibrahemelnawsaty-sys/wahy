<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * إعادة تسمية العلامة إلى «أثيل مكة» (الدفعة 1، مهامّ 1/2/5).
 * (أ) تثبيت setting site_name = «أثيل مكة» في قاعدة البيانات كي تظهر رغم أيّ قيمة «قيمّ» قديمة محفوظة.
 * (ب) مسح تخصيصات العلامة القديمة في landing_content (hero_title/description/features_title/logo_text)
 *     التي يطبّقها سكربت الزوّار عبر /api/landing/content فتطغى على القالب الجديد (R3).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('settings')) {
            $exists = DB::table('settings')->where('key', 'site_name')->exists();
            if ($exists) {
                DB::table('settings')->where('key', 'site_name')
                    ->update(['value' => 'أثيل مكة', 'type' => 'string', 'updated_at' => now()]);
            } else {
                DB::table('settings')->insert([
                    'key' => 'site_name', 'value' => 'أثيل مكة', 'type' => 'string',
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
            Cache::forget('setting.site_name');
        }

        if (Schema::hasTable('landing_content')) {
            DB::table('landing_content')
                ->whereIn('key', ['hero_title', 'hero_description', 'features_title', 'logo_text'])
                ->delete();
        }
    }

    public function down(): void
    {
        // لا استعادة: العلامة القديمة «قيمّ» غير مرغوبة، وبيانات landing_content المحذوفة لا تُستعاد.
    }
};
