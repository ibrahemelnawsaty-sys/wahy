<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * صفحة إدخال كود 2FA كانت **أحاديّة الاستعمال** فتُنتج «419 Page Expired» على أوّل محاولة صادقة.
 *
 * الجذر: `showTwoFactorVerify()` كان ينادي `session()->regenerateToken()` في معالج **GET**.
 * والجلسة تحتفظ بـ`_token` **واحد**، فأيّ فتحٍ ثانٍ للرابط (إعادة تحميل أثناء انتظار البريد،
 * أو إعادة تسجيل دخول لأنّ الرمز تأخّر، أو تبويب ثانٍ، أو جلبٌ استباقيّ من المتصفّح، أو إعادة
 * تحميل التبويب بعد العودة من تطبيق البريد) يقتل الرمز في النسخة المعروضة أمام المستخدم.
 *
 * وتعليق الكود كان يبرّره بـ«تجديد الجلسة لضمان عدم انتهاء صلاحيتها» — وهذا غير صحيح:
 * `Store::regenerateToken()` يكتب `_token` فقط ولا يمسّ عمر الجلسة ولا الكوكي إطلاقاً.
 *
 * ولا يُخسر أمن بحذفه: تدوير الجلسة يقع في موضعه الصحيح — بعد **نجاح** التحقّق.
 */
class TwoFactorPageNotSingleUseTest extends TestCase
{
    use RefreshDatabase;

    private const PASSWORD = 'correct-horse-battery';

    private function user(): User
    {
        return User::factory()->create([
            'email' => '2fa-' . uniqid() . '@example.com',
            'password' => Hash::make(self::PASSWORD),
            'status' => 'active',
            'role' => UserRole::SchoolAdmin->value,
            'two_factor_enabled' => true,
        ]);
    }

    private function startLogin(): User
    {
        Mail::fake();
        $user = $this->user();
        $this->post('/login', ['email' => $user->email, 'password' => self::PASSWORD]);

        return $user;
    }

    public function test_opening_the_verify_page_does_not_rotate_the_csrf_token(): void
    {
        $this->startLogin();

        $this->get('/two-factor/verify')->assertOk();
        $first = session()->token();

        $this->get('/two-factor/verify')->assertOk();

        $this->assertSame(
            $first,
            session()->token(),
            'فتح الصفحة مرّتين يجب ألّا يُبطِل رمز النسخة المفتوحة أمام المستخدم',
        );
    }

    public function test_resending_the_code_does_not_strand_the_open_page(): void
    {
        // سيناريو واقعيّ: البريد مُصفَّف فيتأخّر، فيضغط المستخدم «إعادة الإرسال» ثمّ يُدخل الرمز.
        $this->startLogin();

        $this->get('/two-factor/verify')->assertOk();
        $before = session()->token();

        $this->post('/two-factor/resend');

        $this->assertSame($before, session()->token(), 'إعادة الإرسال يجب ألّا تُبطِل رمز الصفحة');
    }

    public function test_successful_verification_still_rotates_the_session(): void
    {
        // حارس أمنيّ: منع تثبيت الجلسة يبقى في موضعه الصحيح — بعد المصادقة لا قبلها.
        $user = $this->startLogin();
        $this->get('/two-factor/verify')->assertOk();

        $before = session()->getId();
        $this->post('/two-factor/verify', ['code' => (string) $user->fresh()->two_factor_code])
            ->assertRedirect(route('dashboard'));

        $this->assertNotSame($before, session()->getId(), 'يجب تدوير الجلسة بعد نجاح التحقّق');
        $this->assertAuthenticatedAs($user);
    }

    public function test_csrf_rotation_only_survives_where_it_is_correct(): void
    {
        // تدوير الرمز مشروع في مكانٍ واحد: تسجيل الخروج (مع invalidate). وفي أيّ معالج **قراءة**
        // يجعل الصفحة أحاديّة الاستعمال. نُجرّد التعليقات أوّلاً فلا تُحسب الشروح استدعاءات.
        $offenders = [];
        $dir = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(app_path('Http')));

        foreach ($dir as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $code = '';
            foreach (token_get_all((string) file_get_contents($file->getPathname())) as $tok) {
                if (is_array($tok) && in_array($tok[0], [T_COMMENT, T_DOC_COMMENT], true)) {
                    continue; // تجاهُل التعليقات
                }
                $code .= is_array($tok) ? $tok[1] : $tok;
            }

            if (! str_contains($code, 'regenerateToken()')) {
                continue;
            }

            // القاعدة الحقيقيّة: التدوير مشروع حين يكون **جزءاً من تسجيل خروج**، أي مقروناً
            // بـinvalidate() قبله مباشرة (النمط القياسيّ). أمّا تدويرٌ منفرد — خصوصاً في معالج
            // قراءة — فيجعل الصفحة أحاديّة الاستعمال ويُنتج 419 لمستخدمٍ بريء.
            $total = substr_count($code, 'regenerateToken()');
            $paired = preg_match_all('/invalidate\(\)\s*;\s*\$?[^;]{0,80}regenerateToken\(\)/s', $code);

            if ($paired < $total) {
                $offenders[] = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $file->getPathname());
            }
        }

        $this->assertSame([], $offenders, 'تدوير رمز CSRF في موضع غير مشروع: ' . implode(', ', $offenders));
    }
}
