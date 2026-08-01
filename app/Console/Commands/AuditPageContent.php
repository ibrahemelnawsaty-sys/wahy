<?php

namespace App\Console\Commands;

use App\Support\PageContentScanner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * سكربت تدقيق (ت-١): يمسح كلّ محتوى الصفحات المخزَّن بحثاً عن حمولات XSS مزروعة سابقاً
 * (قبل تحصين الرندرة). سدّ الثغرة لا يزيل ما زُرِع — هذا السكربت يكشفه ويُقرِّره.
 *
 * الاستعمال:
 *   php artisan pages:audit-xss            # تقرير فقط
 *   php artisan pages:audit-xss --json     # مخرَج JSON للأرشفة
 */
class AuditPageContent extends Command
{
    protected $signature = 'pages:audit-xss {--json : إخراج التقرير بصيغة JSON}';

    protected $description = 'تدقيق محتوى الصفحات المخزَّن (page_builder + landing_content) بحثاً عن حمولات XSS';

    public function handle(): int
    {
        $findings = [];
        $scanned = 0;

        // 1) page_builder.json_data (شجرة كتل)
        if (DB::getSchemaBuilder()->hasTable('page_builder')) {
            foreach (DB::table('page_builder')->get(['id', 'slug', 'json_data']) as $row) {
                $scanned++;
                $data = json_decode($row->json_data ?? 'null', true);
                $v = PageContentScanner::scan($data, 'json_data');
                if ($v !== []) {
                    $findings[] = ['table' => 'page_builder', 'id' => $row->id, 'ref' => $row->slug, 'violations' => $v];
                }
            }
        }

        // 2) landing_content.value (key/value)
        if (DB::getSchemaBuilder()->hasTable('landing_content')) {
            foreach (DB::table('landing_content')->get(['id', 'key', 'value', 'type']) as $row) {
                $scanned++;
                $v = PageContentScanner::scan($row->value ?? '', 'value');
                if ($v !== []) {
                    $findings[] = ['table' => 'landing_content', 'id' => $row->id, 'ref' => $row->key . " ({$row->type})", 'violations' => $v];
                }
            }
        }

        $contaminated = count($findings);
        $totalViolations = array_sum(array_map(fn ($f) => count($f['violations']), $findings));

        if ($this->option('json')) {
            $this->line(json_encode([
                'scanned_rows' => $scanned,
                'contaminated_rows' => $contaminated,
                'total_violations' => $totalViolations,
                'findings' => $findings,
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

            return $contaminated > 0 ? self::FAILURE : self::SUCCESS;
        }

        // تقرير بشريّ
        $this->newLine();
        $this->info('════════ تقرير تدقيق XSS لمحتوى الصفحات (ت-١) ════════');
        $this->line("الصفوف المفحوصة:  {$scanned}");
        $this->line("الصفوف الملوَّثة:  {$contaminated}");
        $this->line("مجموع المخالفات: {$totalViolations}");
        $this->newLine();

        if ($contaminated === 0) {
            $this->info('✅ نظيف — لا حمولات مزروعة. البند مُغلَق بعد حفظ هذا التقرير.');

            return self::SUCCESS;
        }

        foreach ($findings as $f) {
            $this->warn("● {$f['table']} #{$f['id']} — {$f['ref']}");
            $rows = array_map(fn ($v) => [$v['path'], $v['kind'], $v['sample']], $f['violations']);
            $this->table(['المسار', 'النوع', 'عيّنة'], $rows);
        }

        $this->newLine();
        $this->error('⚠️ عُثِر على حمولات مزروعة. الإجراء: صحّح/امسح الصفوف أعلاه يدويّاً (أو عبر تعديل ثمّ حفظ — طبقة الحفظ سترفض الحمولة). أعِد التشغيل حتى يصير العدّ صفراً.');
        $this->line('ملاحظة: تحصين الرندرة يمنع تنفيذ هذه الحمولات الآن، لكنّ إزالتها من قاعدة البيانات إلزاميّ لإغلاق البند.');

        return self::FAILURE;
    }
}
