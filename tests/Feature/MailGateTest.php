<?php

namespace Tests\Feature;

use App\Mail\LevelUpMail;
use App\Models\EmailPreference;
use App\Models\Setting;
use App\Models\User;
use App\Services\Mail\MailGate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * البوّابة المركزيّة لبريد الأحداث (خطّة البريد P6): تحترم المفتاح الرئيسيّ + مفتاح النوع + إلغاء الاشتراك.
 */
class MailGateTest extends TestCase
{
    use RefreshDatabase;

    public function test_gate_sends_by_default_then_blocks_on_type_flag_and_optout(): void
    {
        Mail::fake();
        $u = User::factory()->create(['role' => 'student', 'email' => 's@example.com']);

        // مُفعَّل افتراضيًّا ⟶ يُرسَل
        $this->assertTrue(MailGate::send($u, 'level_up', 'event', new LevelUpMail($u, 5)));
        Mail::assertSent(LevelUpMail::class, 1);

        // تعطيل النوع ⟶ يُحجب
        Setting::set('email_type_level_up', false, 'boolean');
        $this->assertFalse(MailGate::send($u, 'level_up', 'event', new LevelUpMail($u, 6)));
        Mail::assertSent(LevelUpMail::class, 1);

        // إعادة تفعيل النوع لكن المستخدم ألغى الاشتراك ⟶ يُحجب
        Setting::set('email_type_level_up', true, 'boolean');
        EmailPreference::create(['user_id' => $u->id, 'unsubscribed_all' => true]);
        $this->assertFalse(MailGate::send($u->fresh(), 'level_up', 'event', new LevelUpMail($u, 7)));
        Mail::assertSent(LevelUpMail::class, 1);
    }

    public function test_master_switch_blocks_all_event_mail(): void
    {
        Mail::fake();
        $u = User::factory()->create(['role' => 'student', 'email' => 'm@example.com']);

        Setting::set('email_master_enabled', false, 'boolean');
        $this->assertFalse(MailGate::send($u, 'badge_earned', 'event', new LevelUpMail($u, 3)));
        Mail::assertNothingSent();
    }
}
