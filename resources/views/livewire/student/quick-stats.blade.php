{{--
    Quick Stats Widget — للطالب.

    يُستخدم في:
        <livewire:student.quick-stats :user-id="$user->id" />

    Reactive: يستجيب لحدث 'activity-completed' من Alpine/JavaScript عبر
        $dispatch('activity-completed')
--}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4" wire:poll.60s>
    {{-- النقاط --}}
    <div class="bg-gradient-to-br from-blue-500 to-blue-700 text-white rounded-xl p-4 shadow-md">
        <div class="text-xs opacity-80">إجمالي النقاط</div>
        <div class="text-2xl font-bold mt-1">
            {{ number_format($this->stats['total_points']) }}
        </div>
        <div class="text-xs opacity-60 mt-1">⭐</div>
    </div>

    {{-- العملات --}}
    <div class="bg-gradient-to-br from-yellow-500 to-orange-600 text-white rounded-xl p-4 shadow-md">
        <div class="text-xs opacity-80">العملات</div>
        <div class="text-2xl font-bold mt-1">
            {{ number_format($this->stats['total_coins']) }}
        </div>
        <div class="text-xs opacity-60 mt-1">🪙</div>
    </div>

    {{-- الشارات --}}
    <div class="bg-gradient-to-br from-purple-500 to-pink-600 text-white rounded-xl p-4 shadow-md">
        <div class="text-xs opacity-80">الشارات</div>
        <div class="text-2xl font-bold mt-1">
            {{ $this->stats['badges_count'] }}
        </div>
        <div class="text-xs opacity-60 mt-1">🏆</div>
    </div>

    {{-- الأنشطة المكتملة --}}
    <div class="bg-gradient-to-br from-emerald-500 to-teal-600 text-white rounded-xl p-4 shadow-md">
        <div class="text-xs opacity-80">الأنشطة</div>
        <div class="text-2xl font-bold mt-1">
            {{ $this->stats['completed_activities'] }}
        </div>
        <div class="text-xs opacity-60 mt-1">✅</div>
    </div>
</div>
