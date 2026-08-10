<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * أدوار وصلاحيّات Spatie. **إلزاميّ التشغيل** — بدونه يفشل assignRole في التسجيل بـ
 * «There is no role named `X` for guard `web`» (خطأ 500). idempotent (findOrCreate/syncPermissions).
 */
class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'view users', 'create users', 'edit users', 'delete users',
            'view schools', 'create schools', 'edit schools', 'delete schools',
            'view activities', 'create activities', 'edit activities', 'delete activities', 'grade activities',
            'view content', 'create content', 'edit content', 'delete content',
            'view reports', 'export reports',
            'view settings', 'edit settings',
            'view backups', 'create backups', 'restore backups', 'delete backups',
            'view activity-logs', 'delete activity-logs',
            'view teams', 'create teams', 'edit teams', 'delete teams',
            'view messages', 'send messages',
            'view ratings', 'create ratings',
            'view tickets', 'manage tickets',
        ];
        foreach ($permissions as $p) {
            Permission::findOrCreate($p);
        }

        // الأدوار الستّة (يجب أن تطابق عمود role في users) — idempotent
        $roles = [
            'super_admin' => Permission::pluck('name')->all(), // كل الصلاحيّات
            'school_admin' => [
                'view users', 'create users', 'edit users',
                'view activities', 'create activities', 'edit activities', 'delete activities', 'grade activities',
                'view content', 'view reports', 'export reports', 'view settings',
                'view teams', 'create teams', 'edit teams', 'delete teams',
                'view messages', 'send messages', 'view ratings',
            ],
            'teacher' => [
                'view activities', 'create activities', 'edit activities', 'delete activities', 'grade activities',
                'view content', 'view reports',
                'view teams', 'create teams', 'edit teams', 'delete teams',
                'view messages', 'send messages', 'view ratings',
            ],
            'student' => [
                'view activities', 'view content', 'view teams', 'view messages', 'send messages', 'create ratings',
            ],
            'parent' => [
                'view activities', 'view content', 'view reports', 'view messages', 'send messages',
            ],
            'technical_support' => [
                'view users', 'view messages', 'send messages', 'view reports', 'view tickets', 'manage tickets',
            ],
        ];
        foreach ($roles as $roleName => $perms) {
            Role::findOrCreate($roleName)->syncPermissions($perms);
        }

        if ($this->command) {
            $this->command->info('✅ جاهز: 6 أدوار + الصلاحيّات (Spatie).');
        }
    }
}
