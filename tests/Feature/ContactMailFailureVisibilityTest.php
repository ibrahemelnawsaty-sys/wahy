<?php

namespace Tests\Feature;

use App\Models\EmailLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * فشل بريد نموذج التواصل يجب أن يصل شاشة «سجلّ البريد» بسببه الحقيقيّ.
 *
 * العطل: ContactController يبتلع الاستثناء في Log::warning، والمستمع RecordEmailActivity لا
 * يملك مُعالِج فشل إطلاقًا (sending ثمّ sent فقط). فيبقى الصفّ «sending» حتى تُحوّله المصالحة
 * markStuckAsFailed إلى «failed» برسالة **عامّة** لا تحمل أيّ تشخيص — فيرى المشرف «فشل»
 * ولا يعرف السبب أبدًا، والسبب الحقيقيّ مدفونٌ في laravel.log.
 */
class ContactMailFailureVisibilityTest extends TestCase
{
    use RefreshDatabase;

    private function payload(): array
    {
        return [
            'full_name' => 'زائر',
            'email' => 'visitor@example.com',
            'user_type' => 'teacher',
            'message' => 'رسالة تجريبيّة',
        ];
    }

    public function test_contact_message_is_saved_even_when_mail_fails(): void
    {
        // «أفضل جهد» يبقى: تعطُّل SMTP لا يُضيّع رسالة الزائر ولا يُظهر له خطأ.
        Mail::shouldReceive('send')->andThrow(new \RuntimeException('SMTP 535 Authentication failed'));

        $this->postJson('/contact', $this->payload())
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('contact_messages', ['email' => 'visitor@example.com']);
    }

    public function test_the_real_smtp_error_reaches_the_mail_log(): void
    {
        Mail::shouldReceive('send')->andThrow(new \RuntimeException('SMTP 535 Authentication failed'));

        $this->postJson('/contact', $this->payload())->assertOk();

        $log = EmailLog::where('to_email', 'visitor@example.com')->latest('id')->first();
        $this->assertNotNull($log, 'يجب تسجيل محاولة فاشلة');
        $this->assertSame('failed', $log->status);
        $this->assertStringContainsString('535', (string) $log->error_message, 'السبب الحقيقيّ لا رسالة عامّة');
    }

    public function test_admin_notification_failure_is_recorded_too(): void
    {
        Mail::shouldReceive('send')->andThrow(new \RuntimeException('Connection could not be established'));

        $this->postJson('/contact', $this->payload())->assertOk();

        $admin = EmailLog::where('to_email', setting('contact_email', 'info@atheel-makkah.com'))
            ->latest('id')->first();
        $this->assertNotNull($admin, 'إشعار الإدارة الفاشل يُسجَّل أيضًا');
        $this->assertStringContainsString('Connection', (string) $admin->error_message);
    }
}
