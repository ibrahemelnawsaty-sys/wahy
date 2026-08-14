<?php

namespace Tests\Feature;

use App\Mail\WeeklyDigestMail;
use App\Models\EmailLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * الملخّص الأسبوعيّ + تقليم السجلّ (خطّة البريد P7).
 */
class EmailDigestTest extends TestCase
{
    use RefreshDatabase;

    public function test_weekly_digest_sends_to_active_and_skips_inactive(): void
    {
        Mail::fake();

        $active = User::factory()->create(['role' => 'student', 'email' => 'active@example.com']);
        $active->points()->create(['points' => 50, 'reason' => 'اختبار']);

        User::factory()->create(['role' => 'student', 'email' => 'idle@example.com']); // بلا نقاط

        $this->artisan('emails:digest-weekly')->assertSuccessful();

        Mail::assertSent(WeeklyDigestMail::class, 1);
        Mail::assertSent(WeeklyDigestMail::class, fn ($m) => $m->hasTo('active@example.com'));
    }

    public function test_prune_logs_removes_old_only(): void
    {
        $old = EmailLog::create(['to_email' => 'old@example.com', 'status' => 'sent']);
        $old->created_at = now()->subDays(200);
        $old->save();

        $new = EmailLog::create(['to_email' => 'new@example.com', 'status' => 'sent']);

        $this->artisan('emails:prune-logs --days=180')->assertSuccessful();

        $this->assertDatabaseMissing('email_logs', ['id' => $old->id]);
        $this->assertDatabaseHas('email_logs', ['id' => $new->id]);
    }
}
