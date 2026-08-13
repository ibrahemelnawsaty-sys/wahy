<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Mail\TwoFactorCodeMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * كود التحقّق الثنائيّ **يجب** أن يُرسَل فوراً (`Mail::send`) لا عبر الطابور (`Mail::queue`).
 *
 * الحادثة: `QUEUE_CONNECTION=database` بلا عامل طابور يعمل على الاستضافة المشتركة
 * (المرجع الوحيد لـ`queue:work` وحدةُ systemd مكتوبة لخادم ليس هو الإنتاج) ⟶ كود الدخول
 * يستقرّ في جدول `jobs` للأبد فلا يصل، بينما «إعادة الإرسال» تستخدم `send()` فتصل فوراً —
 * فيظهر السلوك المُربِك: «الكود لا يأتي، وبالضغط على إعادة الإرسال يأتي».
 *
 * 2FA تدفّقٌ **متزامن بطبيعته**: المستخدم واقفٌ ينتظر الكود، فلا معنى لتأجيله.
 * الاختبار يشمل الويب والـAPI معاً لأنّهما مسارَان مستقلّان (تطابُق مطلوب).
 */
class TwoFactorMailIsImmediateTest extends TestCase
{
    use RefreshDatabase;

    private const PASSWORD = 'correct-horse-battery';

    private function twoFactorUser(): User
    {
        return User::factory()->create([
            'email' => '2fa-' . uniqid() . '@example.com',
            'password' => Hash::make(self::PASSWORD),
            'status' => 'active',
            'role' => UserRole::SchoolAdmin->value,
            'two_factor_enabled' => true,
        ]);
    }

    public function test_web_login_sends_the_code_immediately_not_queued(): void
    {
        Mail::fake();
        $user = $this->twoFactorUser();

        $this->post('/login', [
            'email' => $user->email,
            'password' => self::PASSWORD,
        ]);

        Mail::assertSent(TwoFactorCodeMail::class);
        Mail::assertNotQueued(TwoFactorCodeMail::class); // الطابور = لا يصل بلا عامل
    }

    public function test_api_login_sends_the_code_immediately_not_queued(): void
    {
        Mail::fake();
        $user = $this->twoFactorUser();

        $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => self::PASSWORD,
        ])->assertStatus(200)->assertJson(['code' => '2fa_required']);

        Mail::assertSent(TwoFactorCodeMail::class);
        Mail::assertNotQueued(TwoFactorCodeMail::class);
    }

    public function test_resend_still_sends_immediately(): void
    {
        // حارس تراجع: مسار إعادة الإرسال كان سليماً أصلاً ويجب أن يبقى كذلك.
        Mail::fake();
        $user = $this->twoFactorUser();

        $this->post('/login', ['email' => $user->email, 'password' => self::PASSWORD]);
        Mail::fake(); // تصفير بعد رسالة الدخول

        $this->post('/two-factor/resend');

        Mail::assertSent(TwoFactorCodeMail::class);
        Mail::assertNotQueued(TwoFactorCodeMail::class);
    }

    public function test_no_mail_dispatch_in_the_app_uses_the_queue(): void
    {
        // حارس شامل: لا يجوز أن يعود أيّ مسار بريد للطابور ما دام لا عامل يُفرغه.
        // إن شُغِّل عامل طابور موثوق مستقبلاً، احذف هذا الحارس عن قصد لا بالصدفة.
        $hits = [];
        $dir = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(app_path()));

        foreach ($dir as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $src = file_get_contents($file->getPathname());
            if ($src !== false && preg_match('/->(queue|later)\s*\(\s*new\s+\\\\?[A-Za-z_]/', $src)) {
                $hits[] = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $file->getPathname());
            }
        }

        $this->assertSame([], $hits, 'إرسال بريد عبر الطابور في: ' . implode(', ', $hits));
    }
}
