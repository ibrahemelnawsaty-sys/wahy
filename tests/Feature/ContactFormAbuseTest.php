<?php

namespace Tests\Feature;

use App\Mail\ContactConfirmationMail;
use App\Mail\ContactMessageReceivedMail;
use App\Models\ContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * تحصين نموذج «تواصل معنا» بعد حادثة القصف (1377 رسالة/5 دقائق + backscatter بريديّ).
 */
class ContactFormAbuseTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $o = []): array
    {
        return array_merge([
            'full_name' => 'زائر حقيقيّ',
            'email' => 'visitor@example.com',
            'user_type' => 'teacher',
            'message' => 'استفسار عن المنصّة والقيم.',
            'form_ts' => Crypt::encrypt(now()->timestamp - 5), // فُتِح قبل 5 ثوانٍ (بشريّ)
        ], $o);
    }

    public function test_valid_submission_saves_and_notifies_admin_but_never_confirms_submitter(): void
    {
        Mail::fake();

        $this->postJson('/contact', $this->payload())
            ->assertOk()->assertJson(['success' => true]);

        $this->assertDatabaseHas('contact_messages', ['email' => 'visitor@example.com']);
        Mail::assertQueued(ContactMessageReceivedMail::class);           // الأدمن يُشعَر
        Mail::assertNotQueued(ContactConfirmationMail::class);           // ← لا backscatter لعنوان المُرسِل
    }

    public function test_honeypot_filled_is_silently_dropped(): void
    {
        Mail::fake();

        $this->postJson('/contact', $this->payload(['website' => 'http://spam.example']))
            ->assertOk()->assertJson(['success' => true]);

        $this->assertDatabaseCount('contact_messages', 0);
        Mail::assertNothingQueued();
    }

    public function test_instant_submission_is_dropped_by_time_trap(): void
    {
        Mail::fake();

        // فُتِح «الآن» → أسرع من 3 ثوانٍ = بوت
        $this->postJson('/contact', $this->payload(['form_ts' => Crypt::encrypt(now()->timestamp)]))
            ->assertOk()->assertJson(['success' => true]);

        $this->assertDatabaseCount('contact_messages', 0);
        Mail::assertNothingQueued();
    }

    public function test_duplicate_content_creates_only_one_row(): void
    {
        Mail::fake();
        $p = $this->payload(['email' => 'dup@example.com', 'message' => 'نفس الرسالة المكرّرة']);

        $this->postJson('/contact', $p)->assertOk();
        $this->postJson('/contact', $p)->assertOk();

        $this->assertSame(1, ContactMessage::where('email', 'dup@example.com')->count());
    }

    public function test_missing_form_ts_still_accepted_stale_cache_safe(): void
    {
        Mail::fake();
        $p = $this->payload();
        unset($p['form_ts']); // صفحة مُخزَّنة قديمة بلا الحقل — يجب ألّا تُحجب

        $this->postJson('/contact', $p)->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseHas('contact_messages', ['email' => 'visitor@example.com']);
    }
}
