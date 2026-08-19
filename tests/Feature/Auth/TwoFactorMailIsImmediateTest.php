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
 * كل بريدٍ يُطلَق من **طلب ويب** يجب أن يُصفَّف، لا أن يُرسَل متزامنًا.
 *
 * انقلاب مقصود لفرضيّة سابقة: كان هذا الملفّ يفرض العكس (`assertSent`) بناءً على استنتاج أنّ
 * لا عامل طابور يعمل. الدليل الحاسم من الإنتاج نقض ذلك:
 *
 *   Connection could not be established with host "smtp.office365.com:587":
 *   Unable to connect ... (Connection timed out)
 *
 * ظهر في سجلّ البريد لبريد نموذج التواصل (متزامن من الويب)، بينما **حملات البريد تصل** وهي
 * مُصفَّفة يُرسلها العامل من CLI. أي أنّ عمليّة الويب على هذه الاستضافة محجوبة عن المنفذ 587،
 * والعامل هو المسار الوحيد الذي يُوصِل بريدًا. فالإرسال المتزامن = بريدٌ لا يصل أبدًا.
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

    public function test_web_login_queues_the_code(): void
    {
        Mail::fake();
        $user = $this->twoFactorUser();

        $this->post('/login', ['email' => $user->email, 'password' => self::PASSWORD]);

        Mail::assertQueued(TwoFactorCodeMail::class);
        Mail::assertNotSent(TwoFactorCodeMail::class); // المتزامن محجوب على الخادم
    }

    public function test_api_login_queues_the_code(): void
    {
        Mail::fake();
        $user = $this->twoFactorUser();

        $this->postJson('/api/v1/login', ['email' => $user->email, 'password' => self::PASSWORD])
            ->assertStatus(200)->assertJson(['code' => '2fa_required']);

        Mail::assertQueued(TwoFactorCodeMail::class);
        Mail::assertNotSent(TwoFactorCodeMail::class);
    }

    public function test_resend_queues_too(): void
    {
        Mail::fake();
        $user = $this->twoFactorUser();

        $this->post('/login', ['email' => $user->email, 'password' => self::PASSWORD]);
        Mail::fake();

        $this->post('/two-factor/resend');

        Mail::assertQueued(TwoFactorCodeMail::class);
    }

    public function test_no_web_request_path_sends_mail_synchronously(): void
    {
        // الحارس الجوهريّ: أيّ `->send(` أو `Mail::send(` في مسار طلب = بريدٌ لا يصل على هذا
        // الخادم. إن زال الحجب مستقبلًا، وسِّع هذا عن قصد لا بالصدفة.
        $hits = [];
        foreach ([app_path('Http'), app_path('Services'), app_path('Listeners')] as $dir) {
            if (! is_dir($dir)) {
                continue;
            }
            $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));
            foreach ($it as $file) {
                if (! $file->isFile() || $file->getExtension() !== 'php') {
                    continue;
                }
                $src = (string) file_get_contents($file->getPathname());
                if (preg_match('/Mail::send\s*\(|->send\s*\(\s*new\s+\\?[A-Za-z_]/', $src)) {
                    $hits[] = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $file->getPathname());
                }
            }
        }

        $this->assertSame([], $hits, 'إرسال بريد متزامن في: ' . implode(', ', $hits));
    }
}
