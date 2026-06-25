<?php

namespace App\Enums;

/**
 * أدوار المستخدمين في المنصة.
 *
 * يستبدل ~100+ موضع كانت تستخدم string literal مثل 'student' / 'teacher'.
 * استخدم: UserRole::Student->value للمقارنة مع DB، أو UserRole::Student كـ enum.
 *
 * متوافق مع cast في User::$casts['role'] => UserRole::class.
 * عند تفعيل الـ cast، استخدم: $user->role === UserRole::Student
 *
 * ملاحظة: لتفعيل الـ cast بأمان، يجب أولاً التأكد أن جميع نقاط القراءة
 * تستخدم ->value أو مقارنة Enum، وليس string literal مع $user->role.
 * حالياً الـ Enum متاح للاستخدام لكن الـ cast غير مُفعّل (للتوافق الخلفي).
 */
enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case SchoolAdmin = 'school_admin';
    case Teacher = 'teacher';
    case Student = 'student';
    case Parent = 'parent';

    /**
     * التسمية بالعربي للعرض.
     */
    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'مدير عام',
            self::SchoolAdmin => 'مدير مدرسة',
            self::Teacher => 'معلم',
            self::Student => 'طالب',
            self::Parent => 'ولي أمر',
        };
    }

    /**
     * هل هذا الدور إداري؟ (يدير مدرسة أو المنصة كاملة).
     */
    public function isAdmin(): bool
    {
        return in_array($this, [self::SuperAdmin, self::SchoolAdmin], true);
    }

    /**
     * هل هذا الدور مرتبط بمدرسة (وليس super_admin)؟
     */
    public function isScopedToSchool(): bool
    {
        return $this !== self::SuperAdmin;
    }

    /**
     * كل الأدوار كـ array من قيم strings (للـ validation).
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * كل الأدوار كـ array key=>label (للقوائم المنسدلة).
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($r) => [$r->value => $r->label()])
            ->toArray();
    }

    /**
     * تحويل آمن من string (يرجع null لو القيمة غير صحيحة).
     */
    public static function tryFromString(?string $value): ?self
    {
        return $value ? self::tryFrom($value) : null;
    }
}
