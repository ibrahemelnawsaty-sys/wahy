<?php

namespace Database\Seeders;

use App\Models\Concept;
use App\Models\Meaning;
use App\Models\Value;
use Illuminate\Database\Seeder;

class ConceptsSeeder extends Seeder
{
    public function run(): void
    {
        $honesty = Value::where('name', 'الأمانة')->first();

        if ($honesty) {
            // مفهوم: حفظ الحقوق
            $concept1 = Concept::create([
                'value_id' => $honesty->id,
                'name' => 'حفظ الحقوق',
                'description' => 'الحفاظ على حقوق الآخرين وعدم التعدي عليها',
                'order' => 1,
            ]);

            // معنى تحت المفهوم
            Meaning::create([
                'concept_id' => $concept1->id,
                'name' => 'رد الأمانات',
                'description' => 'إعادة ما استُؤمن عليه إلى صاحبه',
                'order' => 1,
            ]);

            // مفهوم: الوفاء بالعهد
            $concept2 = Concept::create([
                'value_id' => $honesty->id,
                'name' => 'الوفاء بالعهد',
                'description' => 'الالتزام بالوعود والمواثيق',
                'order' => 2,
            ]);

            Meaning::create([
                'concept_id' => $concept2->id,
                'name' => 'إنجاز الوعود',
                'description' => 'تنفيذ ما وعدت به الآخرين',
                'order' => 1,
            ]);
        }
    }
}
