<?php

namespace Database\Seeders;

use App\Models\Concept;
use App\Models\Value;
use Illuminate\Database\Seeder;

class ConceptsSeeder extends Seeder
{
    public function run(): void
    {
        $honesty = Value::where('name', 'الأمانة')->first();

        if ($honesty) {
            // مفهوم: حفظ الحقوق (الدروس تُربط مباشرةً بالمفهوم بعد إزالة «المعاني»)
            Concept::firstOrCreate(
                ['value_id' => $honesty->id, 'name' => 'حفظ الحقوق'],
                ['description' => 'الحفاظ على حقوق الآخرين وعدم التعدي عليها', 'order' => 1]
            );

            // مفهوم: الوفاء بالعهد
            Concept::firstOrCreate(
                ['value_id' => $honesty->id, 'name' => 'الوفاء بالعهد'],
                ['description' => 'الالتزام بالوعود والمواثيق', 'order' => 2]
            );
        }
    }
}
