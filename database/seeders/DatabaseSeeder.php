<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PermissionsSeeder::class, // أوّلاً: أدوار Spatie لازمة قبل إسناد الأدوار (assignRole)
            UsersSeeder::class,
            ValuesSeeder::class,
            ConceptsSeeder::class,
            LessonsSeeder::class,
            BadgesSeeder::class,
        ]);
    }
}
