<?php

namespace App\Livewire\Student;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Livewire QuickStats — ودجت ديناميكي للطالب يعرض النقاط/العملات/الشارات.
 *
 * مثال migration من Alpine.js → Livewire:
 *   قبل: <div x-data="{ stats: {} }" x-init="fetchStats()">...</div>
 *   بعد: <livewire:student.quick-stats :user-id="$user->id" />
 *
 * الفوائد:
 *   - الـ state مُدار في PHP (لا تكرار منطق)
 *   - يستجيب لـ events (ActivityCompleted) ويُحدّث نفسه
 *   - SSR-friendly — يُرسم في الـ HTML الأولي
 */
class QuickStats extends Component
{
    public int $userId;

    public function mount(int $userId): void
    {
        $this->userId = $userId;
    }

    /**
     * Computed property — يُحسب عند الطلب فقط، مع cache 60 ثانية.
     */
    #[Computed]
    public function stats(): array
    {
        return Cache::remember(
            "student.quickstats.{$this->userId}",
            60,
            fn () => $this->buildStats(),
        );
    }

    private function buildStats(): array
    {
        $user = User::find($this->userId);

        if (! $user) {
            return [
                'total_points' => 0,
                'total_coins' => 0,
                'badges_count' => 0,
                'completed_activities' => 0,
            ];
        }

        return [
            'total_points' => $user->points()->sum('points'),
            'total_coins' => $user->coins()->sum('coins'),
            'badges_count' => $user->badges()->count(),
            'completed_activities' => $user->activitySubmissions()->done()->count(),
        ];
    }

    /**
     * استمع لحدث ActivityCompleted ل refresh الإحصائيات.
     */
    #[On('activity-completed')]
    public function refresh(): void
    {
        Cache::forget("student.quickstats.{$this->userId}");
        // unset() يجبر Livewire على إعادة حساب @computed
        unset($this->stats);
    }

    public function render()
    {
        return view('livewire.student.quick-stats');
    }
}
