<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Permissions

        // User Management
        Permission::create(['name' => 'view users']);
        Permission::create(['name' => 'create users']);
        Permission::create(['name' => 'edit users']);
        Permission::create(['name' => 'delete users']);

        // School Management
        Permission::create(['name' => 'view schools']);
        Permission::create(['name' => 'create schools']);
        Permission::create(['name' => 'edit schools']);
        Permission::create(['name' => 'delete schools']);

        // Activity Management
        Permission::create(['name' => 'view activities']);
        Permission::create(['name' => 'create activities']);
        Permission::create(['name' => 'edit activities']);
        Permission::create(['name' => 'delete activities']);
        Permission::create(['name' => 'grade activities']);

        // Content Management
        Permission::create(['name' => 'view content']);
        Permission::create(['name' => 'create content']);
        Permission::create(['name' => 'edit content']);
        Permission::create(['name' => 'delete content']);

        // Reports
        Permission::create(['name' => 'view reports']);
        Permission::create(['name' => 'export reports']);

        // Settings
        Permission::create(['name' => 'view settings']);
        Permission::create(['name' => 'edit settings']);

        // Backups
        Permission::create(['name' => 'view backups']);
        Permission::create(['name' => 'create backups']);
        Permission::create(['name' => 'restore backups']);
        Permission::create(['name' => 'delete backups']);

        // Activity Logs
        Permission::create(['name' => 'view activity-logs']);
        Permission::create(['name' => 'delete activity-logs']);

        // Teams
        Permission::create(['name' => 'view teams']);
        Permission::create(['name' => 'create teams']);
        Permission::create(['name' => 'edit teams']);
        Permission::create(['name' => 'delete teams']);

        // Messages
        Permission::create(['name' => 'view messages']);
        Permission::create(['name' => 'send messages']);

        // Ratings
        Permission::create(['name' => 'view ratings']);
        Permission::create(['name' => 'create ratings']);

        // Create Roles and Assign Permissions

        // Super Admin - Full Access
        $superAdmin = Role::create(['name' => 'super_admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // School Admin
        $schoolAdmin = Role::create(['name' => 'school_admin']);
        $schoolAdmin->givePermissionTo([
            'view users', 'create users', 'edit users',
            'view activities', 'create activities', 'edit activities', 'delete activities', 'grade activities',
            'view content',
            'view reports', 'export reports',
            'view settings',
            'view teams', 'create teams', 'edit teams', 'delete teams',
            'view messages', 'send messages',
            'view ratings',
        ]);

        // Teacher
        $teacher = Role::create(['name' => 'teacher']);
        $teacher->givePermissionTo([
            'view activities', 'create activities', 'edit activities', 'delete activities', 'grade activities',
            'view content',
            'view reports',
            'view teams', 'create teams', 'edit teams', 'delete teams',
            'view messages', 'send messages',
            'view ratings',
        ]);

        // Student
        $student = Role::create(['name' => 'student']);
        $student->givePermissionTo([
            'view activities',
            'view content',
            'view teams',
            'view messages', 'send messages',
            'create ratings',
        ]);

        // Parent
        $parent = Role::create(['name' => 'parent']);
        $parent->givePermissionTo([
            'view activities',
            'view content',
            'view reports',
            'view messages', 'send messages',
        ]);

        $this->command->info('✅ Permissions and Roles created successfully!');
    }
}
