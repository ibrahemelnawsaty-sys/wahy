<?php

namespace Tests\Feature;

use App\Mail\ContactConfirmationMail;
use App\Mail\ContactMessageReceivedMail;
use App\Mail\ContactVerificationCodeMail;
use App\Models\ContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * تحصين نموذج «تواصل معنا» بعد حادثة القصف (1377 رسالة/5 دقائق + backscatter بريديّ):
 * حاجز «إثبات تنفيذ JS» (cc_token) + إثبات ملكيّة البريد برمز OTP يُستهلَك مرّة واحدة. البوت
 * الذي يقصف بعناوين لا يملك صناديقها لا يستطيع قراءة الرمز أبداً — فلا يعبر store().
 */
class ContactFormAbuseTest extends TestCase
{
    use RefreshDatabase;

    /** رمز JS مُوقَّع فُتِح قبل 5 ثوانٍ (يُحاكي تنفيذ JS البشريّ). */
    private function ccToken(int $ageSeconds = 5): string
    {
        return Crypt::encrypt(now()->timestamp - $ageSeconds);
    }

    /** يزرع رمز تحقّق صالحاً لبريدٍ (كما لو أُرسِل في الخطوة 1). */
    private function seedCode(string $email = 'visitor@example.com', string $code = '123456'): string
    {
        Cache::put('contact_code:' . sha1(strtolower(trim($email))), hash('sha256', $code), now()->addMinutes(10));

        return $code;
    }

    private function storePayload(array $o = []): array
    {
        return array_merge([
            'full_name' => 'زائر حقيقيّ',
            'email' => 'visitor@example.com',
            'user_type' => 'teacher',
            'message' => 'استفسار عن المنصّة والقيم.',
            'cc_token' => $this->ccToken(),
            'code' => '123456',
        ], $o);
    }

    // ---------- الخطوة 1: إرسال الرمز ----------

    public function test_send_code_queues_a_verification_code_to_the_entered_email(): void
    {
        Mail::fake();

        $this->postJson('/contact/send-code', [
            'email' => 'visitor@example.com',
            'cc_token' => $this->ccToken(),
        ])->assertOk()->assertJson(['success' => true, 'need_code' => true]);

        Mail::assertQueued(ContactVerificationCodeMail::class, fn ($m) => $m->hasTo('visitor@example.com'));
        // مُخزَّن مُجزّأً (لا نصّاً صريحاً)
        $this->assertNotNull(Cache::get('contact_code:' . sha1('visitor@example.com')));
    }

    public function test_send_code_without_js_token_sends_no_mail(): void
    {
        Mail::fake();

        // لا cc_token → بوت: نجاح كاذب صامت بلا بريد (يمنع قصف صناديق الضحايا بالرموز).
        $this->postJson('/contact/send-code', ['email' => 'victim@example.com'])->assertOk();

        Mail::assertNothingQueued();
    }

    public function test_send_code_honeypot_sends_no_mail(): void
    {
        Mail::fake();

        $this->postJson('/contact/send-code', [
            'email' => 'victim@example.com',
            'cc_token' => $this->ccToken(),
            'website' => 'http://spam.example',
        ])->assertOk();

        Mail::assertNothingQueued();
    }

    // ---------- الخطوة 2: حفظ الرسالة ----------

    public function test_valid_submission_with_code_saves_and_notifies_admin_but_never_confirms_submitter(): void
    {
        Mail::fake();
        $this->seedCode();

        $this->postJson('/contact', $this->storePayload())
            ->assertOk()->assertJson(['success' => true]);

        $this->assertDatabaseHas('contact_messages', ['email' => 'visitor@example.com']);
        Mail::assertQueued(ContactMessageReceivedMail::class);   // الأدمن يُشعَر
        Mail::assertNotQueued(ContactConfirmationMail::class);   // ← لا backscatter لعنوان المُرسِل
    }

    public function test_submission_without_a_code_is_rejected_and_not_stored(): void
    {
        Mail::fake();
        // لا نزرع رمزاً — هذا ناقل الهجوم: البوت بعنوانٍ لا يملكه لا يحصل على رمز.
        $p = $this->storePayload();
        unset($p['code']);

        $this->postJson('/contact', $p)->assertStatus(422)->assertJson(['success' => false]);

        $this->assertDatabaseCount('contact_messages', 0);
        Mail::assertNothingQueued();
    }

    public function test_submission_with_wrong_code_is_rejected(): void
    {
        Mail::fake();
        $this->seedCode('visitor@example.com', '123456');

        $this->postJson('/contact', $this->storePayload(['code' => '000000']))->assertStatus(422);

        $this->assertDatabaseCount('contact_messages', 0);
    }

    public function test_code_is_invalidated_after_five_wrong_attempts(): void
    {
        Mail::fake();
        // نعزل منطق عدّاد المحاولات عن محدِّد المسار (الذي يقصر أصلاً على 3/بريد/يوم).
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);
        $this->seedCode('brute@example.com', '135790');

        // 5 محاولات خاطئة تُبطِل الرمز (سدّ التخمين التدريجيّ)…
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/contact', $this->storePayload(['email' => 'brute@example.com', 'code' => '000000']))
                ->assertStatus(422);
        }
        // …فحتّى الرمز الصحيح لم يعد يُقبَل بعدها.
        $this->postJson('/contact', $this->storePayload(['email' => 'brute@example.com', 'code' => '135790']))
            ->assertStatus(422);

        $this->assertDatabaseCount('contact_messages', 0);
    }

    public function test_code_is_single_use(): void
    {
        Mail::fake();
        $this->seedCode();

        $this->postJson('/contact', $this->storePayload())->assertOk();
        // إعادة استخدام نفس الرمز يُرفَض (استُهلِك).
        $this->postJson('/contact', $this->storePayload(['message' => 'رسالة مختلفة تماماً']))->assertStatus(422);

        $this->assertSame(1, ContactMessage::count());
    }

    public function test_honeypot_filled_is_silently_dropped(): void
    {
        Mail::fake();
        $this->seedCode();

        $this->postJson('/contact', $this->storePayload(['website' => 'http://spam.example']))
            ->assertOk()->assertJson(['success' => true]);

        $this->assertDatabaseCount('contact_messages', 0);
        Mail::assertNothingQueued();
    }

    public function test_submission_without_js_token_is_silently_dropped(): void
    {
        Mail::fake();
        $this->seedCode();
        $p = $this->storePayload();
        unset($p['cc_token']); // بوت لا ينفّذ JS

        $this->postJson('/contact', $p)->assertOk()->assertJson(['success' => true]);

        $this->assertDatabaseCount('contact_messages', 0);
        Mail::assertNothingQueued();
    }

    public function test_instant_submission_is_dropped_by_js_time_trap(): void
    {
        Mail::fake();
        $this->seedCode();

        // فُتِح «الآن» → أسرع من ثانية = بوت
        $this->postJson('/contact', $this->storePayload(['cc_token' => Crypt::encrypt(now()->timestamp)]))
            ->assertOk()->assertJson(['success' => true]);

        $this->assertDatabaseCount('contact_messages', 0);
        Mail::assertNothingQueued();
    }

    public function test_duplicate_content_creates_only_one_row(): void
    {
        Mail::fake();

        $this->seedCode('dup@example.com');
        $this->postJson('/contact', $this->storePayload(['email' => 'dup@example.com', 'message' => 'نفس الرسالة المكرّرة']))->assertOk();

        // رمز جديد للمحاولة الثانية (الأوّل استُهلِك)؛ نفس المحتوى → يُكشَف كتكرار.
        $this->seedCode('dup@example.com');
        $this->postJson('/contact', $this->storePayload(['email' => 'dup@example.com', 'message' => 'نفس الرسالة المكرّرة']))->assertOk();

        $this->assertSame(1, ContactMessage::where('email', 'dup@example.com')->count());
    }
}
