<?php

namespace App\Console\Commands;

use App\Models\School;
use App\Models\SchoolStatisticsCache;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RefreshSchoolStatistics extends Command
{
    protected $signature = 'schools:refresh-stats {--chunk=50 : عدد المدارس في كل دفعة}';

    protected $description = 'إعادة بناء جدول school_statistics_cache من النقاط الفعلية (يُجدوَل ساعةً)';

    public function handle(): int
    {
        $start = microtime(true);
        $chunkSize = (int) $this->option('chunk');
        $processed = 0;

        $this->info('🔄 بدء تحديث إحصائيات المدارس...');

        School::where('status', 'active')
            ->select('id', 'name', 'city')
            ->chunkById($chunkSize, function ($schools) use (&$processed) {
                foreach ($schools as $school) {
                    try {
                        $this->refreshSchool($school);
                        $processed++;
                    } catch (\Throwable $e) {
                        Log::error('فشل تحديث إحصائيات المدرسة', [
                            'school_id' => $school->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            });

        $duration = round(microtime(true) - $start, 2);
        $this->info("✅ تم تحديث {$processed} مدرسة في {$duration}s");

        return self::SUCCESS;
    }

    /**
     * إعادة حساب إحصائيات مدرسة واحدة + ترتيبها داخل platform/city.
     */
    private function refreshSchool(School $school): void
    {
        $totalPoints = (int) DB::table('points')
            ->join('users', 'users.id', '=', 'points.user_id')
            ->where('users.school_id', $school->id)
            ->where('users.role', 'student')
            ->sum('points.points');

        $monthlyPoints = (int) DB::table('points')
            ->join('users', 'users.id', '=', 'points.user_id')
            ->where('users.school_id', $school->id)
            ->where('users.role', 'student')
            ->where('points.created_at', '>=', now()->startOfMonth())
            ->sum('points.points');

        // platform rank
        $platformRank = School::where('status', 'active')
            ->whereRaw(
                '(SELECT COALESCE(SUM(p.points), 0) FROM points p JOIN users u ON u.id = p.user_id WHERE u.school_id = schools.id AND u.role = ?) > ?',
                ['student', $totalPoints],
            )
            ->count() + 1;

        $platformTotal = School::where('status', 'active')->count();

        // city rank (قد يكون city فارغ)
        $cityRank = null;
        $cityTotal = null;
        if (! empty($school->city)) {
            $cityRank = School::where('status', 'active')
                ->where('city', $school->city)
                ->whereRaw(
                    '(SELECT COALESCE(SUM(p.points), 0) FROM points p JOIN users u ON u.id = p.user_id WHERE u.school_id = schools.id AND u.role = ?) > ?',
                    ['student', $totalPoints],
                )
                ->count() + 1;

            $cityTotal = School::where('status', 'active')->where('city', $school->city)->count();
        }

        // الفرق مع المخزون السابق لحساب trend
        $previous = SchoolStatisticsCache::where('entity_type', 'school')
            ->where('entity_id', $school->id)
            ->first();

        $previousPoints = $previous?->total_points ?? 0;
        $pointsChange = $totalPoints - $previousPoints;
        $previousRank = $previous?->platform_rank ?? $platformRank;
        $rankChange = $previousRank - $platformRank; // موجب = ارتفع
        $trend = $pointsChange > 0 ? 'up' : ($pointsChange < 0 ? 'down' : 'flat');

        SchoolStatisticsCache::updateOrCreate(
            [
                'entity_type' => 'school',
                'entity_id' => $school->id,
            ],
            [
                'school_id' => $school->id,
                'total_points' => $totalPoints,
                'previous_points' => $previousPoints,
                'points_change' => $pointsChange,
                'monthly_points' => $monthlyPoints,
                'platform_rank' => $platformRank,
                'platform_total' => $platformTotal,
                'city_rank' => $cityRank,
                'city_total' => $cityTotal,
                'city' => $school->city,
                'trend' => $trend,
                'rank_change' => $rankChange,
                'calculated_at' => now(),
            ],
        );
    }
}
