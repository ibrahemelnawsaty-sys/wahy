<?php

namespace Tests\Feature\Security;

use App\Exports\SchoolsExport;
use App\Exports\StudentsExport;
use App\Exports\TeachersExport;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * 🔴 OWASP CSV/Formula injection: نتأكد أن أي قيمة نصية تبدأ بـ
 * = + - @ أو tab/CR يتم تحييدها بإضافة علامة اقتباس مفردة (')
 * قبلها، بينما القيم العادية تبقى كما هي.
 */
class CsvInjectionTest extends TestCase
{
    use RefreshDatabase;

    /** القيمة الخبيثة التي تُنفَّذ كصيغة عند فتح الملف في Excel/Sheets. */
    private const MALICIOUS = '=HYPERLINK("http://evil","x")';

    public function test_students_export_neutralizes_formula_in_name(): void
    {
        $school = School::factory()->create();
        $student = User::factory()->student($school)->create([
            'name' => self::MALICIOUS,
        ]);

        $row = (new StudentsExport)->map($student);

        // العمود الثاني (index 1) هو الاسم.
        $this->assertStringStartsWith("'", $row[1]);
        $this->assertSame("'" . self::MALICIOUS, $row[1]);
    }

    public function test_teachers_export_neutralizes_formula_in_name(): void
    {
        $school = School::factory()->create();
        $teacher = User::factory()->teacher($school)->create([
            'name' => self::MALICIOUS,
        ]);

        $row = (new TeachersExport)->map($teacher);

        $this->assertStringStartsWith("'", $row[1]);
        $this->assertSame("'" . self::MALICIOUS, $row[1]);
    }

    public function test_schools_export_neutralizes_formula_in_name(): void
    {
        $school = School::factory()->create([
            'name' => self::MALICIOUS,
        ]);
        // map() يقرأ withCount aliases — نعيد تحميل النموذج بنفس استعلام الـ export.
        $school = (new SchoolsExport)->collection()->firstWhere('id', $school->id);

        $row = (new SchoolsExport)->map($school);

        $this->assertStringStartsWith("'", $row[1]);
        $this->assertSame("'" . self::MALICIOUS, $row[1]);
    }

    public function test_normal_name_is_not_prefixed(): void
    {
        $school = School::factory()->create();
        $student = User::factory()->student($school)->create([
            'name' => 'Ali',
        ]);

        $row = (new StudentsExport)->map($student);

        $this->assertSame('Ali', $row[1]);
        $this->assertStringStartsNotWith("'", $row[1]);
    }
}
