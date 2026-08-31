<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * إصلاح عطلَي البريد الحيّين:
 * (13) نموذج التواصل كان يُرسل لنطاق مهجور info@sa-salem.com بدل setting('contact_email').
 * (25) تعطُّل SMTP كان يُرجِع 500 ويُظهر خطأً رغم حفظ الرسالة — صار «أفضل جهد» لا يُضيّعها.
 */
class ContactFormResilienceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // إثبات ملكيّة البريد صار مطلوباً: نزرع رمزاً صالحاً (كما لو أُرسِل في الخطوة 1).
        Cache::put('contact_code:' . sha1('visitor@example.com'), hash('sha256', '123456'), now()->addMinutes(10));
    }

    private function payload(array $o = []): array
    {
        return array_merge([
            'full_name' => 'أحمد التجريبيّ',
            'email' => 'visitor@example.com',
            'user_type' => 'teacher',
            'message' => 'رسالة اختبار للتواصل.',
            'cc_token' => Crypt::encrypt(now()->timestamp - 5), // إثبات تنفيذ JS
            'code' => '123456',                                 // رمز التحقّق المزروع
        ], $o);
    }

    public function test_contact_saves_and_returns_success(): void
    {
        Mail::fake();

        $this->postJson('/contact', $this->payload())
            ->assertOk()->assertJsonPath('success', true);

        $this->assertDatabaseHas('contact_messages', ['email' => 'visitor@example.com']);
    }

    public function test_contact_survives_smtp_failure_without_losing_message(): void
    {
        // محاكاة تعطّل SMTP: كلّ إرسال يرمي — يجب ألّا يُضيّع الرسالة ولا يُظهر خطأً للزائر.
        Mail::shouldReceive('send')->andThrow(new \RuntimeException('SMTP down'));

        $this->postJson('/contact', $this->payload())
            ->assertOk()->assertJsonPath('success', true);

        $this->assertSame(1, ContactMessage::where('email', 'visitor@example.com')->count());
    }

    public function test_recipient_comes_from_setting_not_dead_domain(): void
    {
        // حارس انحدار: النطاق المهجور لا يعود، والمستقبِل من الإعداد.
        $src = file_get_contents(app_path('Http/Controllers/ContactController.php'));
        $this->assertStringNotContainsString('sa-salem.com', $src);
        $this->assertStringContainsString("setting('contact_email'", $src);
    }
}
