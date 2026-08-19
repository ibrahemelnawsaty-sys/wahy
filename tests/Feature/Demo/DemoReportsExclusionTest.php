<?php

namespace Tests\Feature\Demo;

use App\Exports\SchoolsExport;
use App\Exports\StudentsExport;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * الدفعة 4 — التقارير والتصدير تستثني حسابات الديمو (بيانات منصّة نقيّة).
 */
class DemoReportsExclusionTest extends TestCase
{
    use RefreshDatabase;

    public function test_reports_dashboard_stats_exclude_demo(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $school = School::factory()->create();
        User::factory()->count(2)->create(['role' => 'student', 'school_id' => $school->id, 'created_at' => now()]);
        User::factory()->create(['role' => 'student', 'school_id' => $school->id, 'is_demo' => true, 'created_at' => now()]);

        $res = $this->actingAs($admin)->get(route('admin.reports.dashboard'))->assertOk();
        $this->assertSame(2, $res->viewData('stats')['total_students']);
    }

    public function test_students_export_excludes_demo(): void
    {
        $school = School::factory()->create();
        $real = User::factory()->create(['role' => 'student', 'school_id' => $school->id]);
        $demo = User::factory()->create(['role' => 'student', 'school_id' => $school->id, 'is_demo' => true]);

        $ids = (new StudentsExport)->collection()->pluck('id')->all();
        $this->assertContains($real->id, $ids);
        $this->assertNotContains($demo->id, $ids, 'التصدير يستثني حساب الديمو');
    }

    public function test_schools_export_counts_exclude_demo(): void
    {
        $school = School::factory()->create();
        User::factory()->create(['role' => 'student', 'school_id' => $school->id]);
        User::factory()->create(['role' => 'student', 'school_id' => $school->id, 'is_demo' => true]);

        $row = (new SchoolsExport)->collection()->firstWhere('id', $school->id);
        $this->assertNotNull($row);
        $this->assertSame(1, (int) $row->students_count, 'عدّ طلاب المدرسة في التصدير يستثني الديمو');
    }
}
