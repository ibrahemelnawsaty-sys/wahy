<?php

namespace Database\Seeders;

use App\Models\Value;
use Illuminate\Database\Seeder;

class ValuesSeeder extends Seeder
{
    public function run(): void
    {
        $values = [
            ['name' => 'الأمانة', 'description' => 'حفظ الحقوق والوفاء بالعهود', 'icon' => '🤝', 'order' => 1],
            ['name' => 'التعاون', 'description' => 'العمل الجماعي ومساعدة الآخرين', 'icon' => '🤲', 'order' => 2],
            ['name' => 'الإحسان', 'description' => 'إتقان العمل والإحسان للناس', 'icon' => '✨', 'order' => 3],
            ['name' => 'الصدق', 'description' => 'قول الحق والابتعاد عن الكذب', 'icon' => '💎', 'order' => 4],
            ['name' => 'الاحترام', 'description' => 'احترام الآخرين وتقديرهم', 'icon' => '🙏', 'order' => 5],
        ];

        foreach ($values as $value) {
            Value::create($value);
        }
    }
}
