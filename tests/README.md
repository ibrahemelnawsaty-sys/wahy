# 🧪 Tests Suite — منصة قيمّ

## التشغيل

```bash
# تشغيل كل الاختبارات
composer test
# أو:
php artisan test

# Unit فقط
composer test:unit

# Feature فقط
composer test:feature

# مع coverage
composer test:coverage

# فحص أسلوب الكود
composer lint
composer lint:fix

# تحليل ثابت
composer analyse

# CI كامل (lint + analyse + test)
composer ci
```

## البنية

```
tests/
├── TestCase.php                      # base class
├── CreatesApplication.php
├── Unit/
│   └── Enums/
│       └── UserRoleTest.php          # تحقق من Enum methods
└── Feature/
    ├── Auth/
    │   ├── LoginTest.php             # تسجيل دخول، logout، redirects
    │   └── RegisterTest.php          # تسجيل + 🔴 SEC-001 منع admin escalation
    ├── Authorization/
    │   ├── ActivityPolicyTest.php           # 7 سيناريوهات
    │   ├── ActivitySubmissionPolicyTest.php # scoping بالمدرسة
    │   └── MessagePolicyTest.php            # sender/receiver فقط
    ├── Gamification/
    │   └── GamificationServiceTest.php  # transactions + level up + race
    └── Security/
        └── MassAssignmentGuardsTest.php  # role/status/school_id حماية
```

## قاعدة البيانات

- **محرّك:** SQLite in-memory (`:memory:`)
- **Reset:** كل اختبار يستخدم `RefreshDatabase` trait — fresh DB لكل test
- **APP_KEY:** ثابت في `phpunit.xml` (لا يحتاج `.env` للاختبارات)

## Factories الجاهزة

| Factory | استدعاء |
|---|---|
| User | `User::factory()->student()->create()` |
| User (admin) | `User::factory()->superAdmin()->create()` |
| User (teacher in school) | `User::factory()->teacher($school)->create()` |
| School | `School::factory()->create()` |
| Classroom | `Classroom::factory()->create(['school_id' => $school->id])` |
| Activity | `Activity::factory()->create()` أو `->pendingApproval()` |
| ActivitySubmission | `ActivitySubmission::factory()->approved(85)->create()` |
| Lesson, Concept, Value, Message | متاحة بالـ default state |

## كيف تكتب test جديد

```php
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MyFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_my_feature(): void
    {
        $user = User::factory()->student()->create();

        $response = $this->actingAs($user)->get('/student/dashboard');

        $response->assertOk();
    }
}
```

## ملاحظات مهمة

- 🟡 **الاختبارات الحالية تكشف صحة بنية الكود لكن لا تضمن سلوك الـ HTTP في الإنتاج 100%.** بعض الـ guards (مثل append-only لـ Point/Coin) تتحقق من `runningInConsole()` — في PHPUnit القيمة دائماً `true` لذا لا يتم تطبيق الحماية. للاختبار الكامل استخدم Laravel Dusk (browser tests).

- 🟢 **CI/CD:** أضفنا `.github/workflows/ci.yml` يشغّل lint + analyse + test على كل push/PR.

- 🟠 **PHPStan level 5:** بداية معقولة. ارفعه تدريجياً (level 6 ثم 7) مع تنظيف الكود.

- ⚠️ **Pint:** عند تشغيل `composer lint:fix` لأول مرة، سيقوم بتغيير ~كل ملف PHP في المشروع. اعمل commit قبله.

## التغطية المتوقعة من Sprint 3

| المحور | اختبارات | تغطية حقيقية |
|---|:-:|:-:|
| Auth (login/register/logout) | 13 | ✅ |
| Authorization (4 Policies) | 17 | ✅ |
| Gamification (transactions/level up) | 7 | ⚠️ Append-only يحتاج Dusk |
| Mass Assignment guards | 5 | ⚠️ runningInConsole limitation |
| UserRole Enum | 6 | ✅ |
| **الإجمالي** | **48 test** | **~30% للمسارات الحرجة** |

**الهدف القادم (Sprint 3.5):** زيادة التغطية إلى 60% بإضافة:
- ActivitySubmission flow E2E
- Shop purchase flow
- PvP flow
- Admin user management
