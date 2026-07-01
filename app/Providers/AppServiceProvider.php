<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // تثبيت اللغة العربية على مستوى التطبيق و Carbon — يضمن ظهور
        // diffForHumans / dayName / monthName / translatedFormat بالعربية في كل المنصة.
        App::setLocale('ar');
        Carbon::setLocale('ar');

        // مشاركة الهوية/الثيم/روابط التواصل مع كل القوالب (مصدر موحّد بدل hardcode في كل لايوت)
        if (! $this->app->runningInConsole()) {
            try {
                $branding = \App\Models\Setting::getMany([
                    'site_name', 'site_logo', 'site_favicon', 'site_tagline', 'site_description',
                    'footer_text', 'meta_title', 'meta_description',
                    'primary_color', 'secondary_color', 'text_color', 'background_color', 'font_family', 'site_theme',
                ], [
                    'site_name' => 'قيمّ',
                    'primary_color' => '#667eea',
                    'secondary_color' => '#764ba2',
                    'text_color' => '#1e293b',
                    'background_color' => '#ffffff',
                    'font_family' => 'IBM Plex Sans Arabic',
                    'site_theme' => 'light',
                ]);
                $branding['social_links'] = social_links();
                View::share('branding', $branding);
            } catch (\Throwable $e) {
                // قبل تشغيل المايجريشن قد لا يوجد جدول settings — نتجاهل بأمان
                View::share('branding', ['site_name' => 'قيمّ', 'social_links' => []]);
            }
        }

        // فرض HTTPS في الإنتاج
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Sentry: فلتر بيانات حساسة قبل الإرسال (لا يمكن وضعه في config لأنه closure)
        $this->registerSentryBeforeSend();

        // Rate limiters: api العامة + login المخصص
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
        });

        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->input('email', $request->input('login', ''));

            return [
                Limit::perMinute(5)->by($email . '|' . $request->ip()),
                Limit::perMinute(20)->by($request->ip()),
            ];
        });

        // منح السوبر أدمن جميع الصلاحيات
        Gate::before(function ($user, $ability) {
            return $user->role === 'super_admin' ? true : null;
        });

        // صلاحية الوصول للوحة التحكم
        Gate::define('access-admin', function ($user) {
            return $user->role === 'super_admin';
        });

        // مشاركة بيانات العداد مع لوحات الأدمن
        View::composer(
            ['layouts.admin', 'layouts.super-admin'],
            \App\View\Composers\HeaderDataComposer::class,
        );

        // مشاركة بيانات المستخدم مع جميع Views - محسّن للأداء
        View::composer(['layouts.student-app', 'student.*'], function ($view) {
            if (auth()->check() && auth()->user()->role === 'student') {
                $user = auth()->user();

                // Cache للبيانات لمدة دقيقة واحدة
                $cacheKey = 'student_stats_' . $user->id;
                $stats = Cache::remember($cacheKey, 60, function () use ($user) {
                    return [
                        'total_points' => $user->points()->sum('points'),
                        'total_coins' => $user->coins()->sum('coins'),
                        'total_badges' => $user->badges()->count(),
                    ];
                });

                // ندمج إحصائيات التلعيب الأساسية (للهيدر) مع أي stats مرّرها المتحكّم
                // بدل طمسها — كي لا تُفقَد مفاتيح خاصة بالصفحة مثل current_streak/average_score/
                // completed_activities/total_matches. مفاتيح المتحكّم تفوز عند التعارض.
                $existingStats = $view->getData()['stats'] ?? null;
                $view->with('stats', array_merge($stats, is_array($existingStats) ? $existingStats : []));
                $view->with('streak', $user->streak);
                $view->with('badges', $user->badges);
            }
        });

        // تسجيل Event Listeners للتلعيب
        Event::listen(
            \App\Events\ActivityCompleted::class,
            [\App\Listeners\CheckBadgeEligibility::class, 'handle'],
        );

        Event::listen(
            \App\Events\ActivityCompleted::class,
            [\App\Listeners\UpdateStreak::class, 'handle'],
        );

        Event::listen(
            \App\Events\LevelUp::class,
            [\App\Listeners\CheckBadgeEligibility::class, 'handle'],
        );

        Event::listen(
            \App\Events\StreakUpdated::class,
            [\App\Listeners\CheckBadgeEligibility::class, 'handle'],
        );

        // تسجيل Event Listeners للإشعارات التلقائية
        Event::listen(
            \App\Events\ActivityGraded::class,
            [\App\Listeners\SendActivityGradedNotification::class, 'handle'],
        );

        Event::listen(
            \App\Events\BadgeEarned::class,
            [\App\Listeners\SendBadgeEarnedNotification::class, 'handle'],
        );

        Event::listen(
            \App\Events\StudentRegistered::class,
            [\App\Listeners\SendWelcomeNotification::class, 'handle'],
        );
    }

    /**
     * Sentry beforeSend hook — يفلتر بيانات حساسة قبل إرسالها لـ Sentry.
     *
     * يعمل فقط لو الـ package مثبّت + DSN مُعرَّف.
     * موجود هنا (لا في config) لأن config:cache لا يقبل closures.
     */
    private function registerSentryBeforeSend(): void
    {
        if (! class_exists(\Sentry\State\Hub::class)) {
            return;
        }

        $hub = \Sentry\State\Hub::getCurrent();
        $client = $hub->getClient();
        if (! $client) {
            return;
        }

        $client->getOptions()->setBeforeSendCallback(function (\Sentry\Event $event) {
            $request = $event->getRequest();

            if (! empty($request)) {
                $data = $request['data'] ?? [];

                foreach (['password', 'password_confirmation', 'token', 'api_key', 'secret', 'access_token'] as $sensitive) {
                    if (isset($data[$sensitive])) {
                        $data[$sensitive] = '[FILTERED]';
                    }
                }

                $request['data'] = $data;
                $event->setRequest($request);
            }

            return $event;
        });
    }
}
