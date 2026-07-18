<?php

namespace Tests\Unit\Enums;

use App\Enums\UserRole;
use PHPUnit\Framework\TestCase;

class UserRoleTest extends TestCase
{
    public function test_values_returns_all_role_strings(): void
    {
        $values = UserRole::values();

        $this->assertIsArray($values);
        $this->assertContains('super_admin', $values);
        $this->assertContains('school_admin', $values);
        $this->assertContains('teacher', $values);
        $this->assertContains('student', $values);
        $this->assertContains('parent', $values);
        $this->assertContains('technical_support', $values);
        $this->assertCount(6, $values);
    }

    public function test_options_returns_value_to_label_map(): void
    {
        $options = UserRole::options();

        $this->assertEquals('طالب', $options['student']);
        $this->assertEquals('معلم', $options['teacher']);
        $this->assertEquals('ولي أمر', $options['parent']);
        $this->assertEquals('مدير مدرسة', $options['school_admin']);
        $this->assertEquals('مدير عام', $options['super_admin']);
        $this->assertEquals('الدعم الفنيّ', $options['technical_support']);
    }

    public function test_is_admin_returns_true_for_admin_roles_only(): void
    {
        $this->assertTrue(UserRole::SuperAdmin->isAdmin());
        $this->assertTrue(UserRole::SchoolAdmin->isAdmin());
        $this->assertFalse(UserRole::Teacher->isAdmin());
        $this->assertFalse(UserRole::Student->isAdmin());
        $this->assertFalse(UserRole::Parent->isAdmin());
    }

    public function test_is_scoped_to_school_excludes_super_admin(): void
    {
        $this->assertFalse(UserRole::SuperAdmin->isScopedToSchool());
        $this->assertTrue(UserRole::SchoolAdmin->isScopedToSchool());
        $this->assertTrue(UserRole::Teacher->isScopedToSchool());
        $this->assertTrue(UserRole::Student->isScopedToSchool());
        $this->assertTrue(UserRole::Parent->isScopedToSchool());
        $this->assertFalse(UserRole::TechnicalSupport->isScopedToSchool());
    }

    public function test_try_from_string_handles_null_and_invalid(): void
    {
        $this->assertNull(UserRole::tryFromString(null));
        $this->assertNull(UserRole::tryFromString(''));
        $this->assertNull(UserRole::tryFromString('hacker_admin'));

        $this->assertSame(UserRole::Student, UserRole::tryFromString('student'));
    }

    public function test_label_returns_arabic(): void
    {
        $this->assertEquals('طالب', UserRole::Student->label());
        $this->assertEquals('معلم', UserRole::Teacher->label());
    }
}
