<?php

/**
 * سِجلّ حقول محتوى الصفحة الرئيسية القابلة للتحرير (المصدر الواحد).
 * يقود: (1) نموذج تحرير الأدمن (يُولَّد تلقائيّاً)، (2) العرض الخادميّ عبر lc('key', الافتراضيّ).
 *
 * البنية: قسم => [ 'label' => عنوان القسم، 'fields' => [ مفتاح => [label, type, default] ] ]
 * الأنواع: text | textarea. الافتراضيّ = النصّ الحاليّ في القالب (يظهر إن لم يُحفَظ تخصيص).
 * الإضافة لاحقاً: أضِف الحقل هنا ثمّ لُفّ العنصر في landing.blade.php بـ{{ lc('key', 'default') }}.
 */
return [

    'hero' => [
        'label' => 'الواجهة (الهيرو)',
        'fields' => [
            'hero_title' => ['label' => 'العنوان الرئيسيّ', 'type' => 'textarea', 'default' => 'منصة أثيل مكة.. قيم نبوية يحيى بها الطالب'],
            'hero_description' => ['label' => 'الوصف', 'type' => 'textarea', 'default' => 'من مكة المكرمة .. نبع الهدايات، انطلقت منصتنا تلهم الواردين من الطلاب والمعلمين.. تجمع بين المتعة والفائدة لتبني جيلاً فعالاً متخلقاً بقيم وأخلاق النبوة'],
            'hero_btn_primary' => ['label' => 'زرّ أساسيّ', 'type' => 'text', 'default' => 'ابدأ الآن'],
            'hero_btn_secondary' => ['label' => 'زرّ ثانويّ', 'type' => 'text', 'default' => 'اعرف المزيد'],
        ],
    ],

    'features' => [
        'label' => 'قسم المزايا',
        'fields' => [
            'features_title' => ['label' => 'عنوان القسم', 'type' => 'text', 'default' => 'لماذا أثيل مكة؟'],
            'features_subtitle' => ['label' => 'العنوان الفرعيّ', 'type' => 'text', 'default' => 'نظام متكامل بمميزات فريدة'],
            'feature_5_title' => ['label' => 'ميزة 1 — العنوان', 'type' => 'text', 'default' => 'تحديات ومنافسات'],
            'feature_5_desc' => ['label' => 'ميزة 1 — الوصف', 'type' => 'textarea', 'default' => 'تحفز المشاركين وترفع مستوى التفكير والإدراك'],
            'feature_6_title' => ['label' => 'ميزة 2 — العنوان', 'type' => 'text', 'default' => 'قياس الأثر'],
            'feature_6_desc' => ['label' => 'ميزة 2 — الوصف', 'type' => 'textarea', 'default' => 'وجود استبانات توضح مدى نسبة الاستفادة من الأنشطة والتمارين لتحقيق الهدف'],
            'feature_7_title' => ['label' => 'ميزة 3 — العنوان', 'type' => 'text', 'default' => 'أنشطة سلوكية'],
            'feature_7_desc' => ['label' => 'ميزة 3 — الوصف', 'type' => 'textarea', 'default' => 'تساعد على تطبيق المفاهيم وتحويلها إلى ممارسات حياتية'],
            'feature_8_title' => ['label' => 'ميزة 4 — العنوان', 'type' => 'text', 'default' => 'تحفيز وتنشيط'],
            'feature_8_desc' => ['label' => 'ميزة 4 — الوصف', 'type' => 'textarea', 'default' => 'يساعدان في رفع المعنويات ودفع التقدم وتحريك الطاقات الكامنة'],
            'feature_9_title' => ['label' => 'ميزة 5 — العنوان', 'type' => 'text', 'default' => 'الانسيابية والسهولة'],
            'feature_9_desc' => ['label' => 'ميزة 5 — الوصف', 'type' => 'textarea', 'default' => 'الوضوح واليسر والمتابعة بانتظام ودون تعقيد وتكلف'],
            'feature_1_title' => ['label' => 'ميزة 6 — العنوان', 'type' => 'text', 'default' => 'QR فريد لكل مستخدم'],
            'feature_1_desc' => ['label' => 'ميزة 6 — الوصف', 'type' => 'textarea', 'default' => 'كل طالب ومعلم لديه رمز QR خاص للدخول السريع وتسجيل الحضور والأنشطة'],
            'feature_2_title' => ['label' => 'ميزة 7 — العنوان', 'type' => 'text', 'default' => 'لوحة صدارة ذكية'],
            'feature_2_desc' => ['label' => 'ميزة 7 — الوصف', 'type' => 'textarea', 'default' => 'نظام تنافسي محفز يعرض أفضل الطلاب والفرق بناءً على الإنجازات والنقاط'],
            'feature_3_title' => ['label' => 'ميزة 8 — العنوان', 'type' => 'text', 'default' => 'اقتراح أنشطة بالذكاء الاصطناعي'],
            'feature_3_desc' => ['label' => 'ميزة 8 — الوصف', 'type' => 'textarea', 'default' => 'نظام ذكي يقترح أنشطة مخصصة لكل طالب حسب مستواه واهتماماته'],
            'feature_4_title' => ['label' => 'ميزة 9 — العنوان', 'type' => 'text', 'default' => 'متابعة وتقييم المعلمين'],
            'feature_4_desc' => ['label' => 'ميزة 9 — الوصف', 'type' => 'textarea', 'default' => 'أدوات شاملة لمتابعة أداء الطلاب وتقييمهم بطرق متنوعة ومرنة'],
        ],
    ],

    'values' => [
        'label' => 'قسم «كيف نبني القيم؟»',
        'fields' => [
            'values_title' => ['label' => 'عنوان القسم', 'type' => 'text', 'default' => 'كيف نبني القيم؟'],
            'values_subtitle' => ['label' => 'العنوان الفرعيّ', 'type' => 'text', 'default' => 'منهجية متكاملة من القيمة إلى التطبيق العملي'],
        ],
    ],

    'contact' => [
        'label' => 'قسم التواصل',
        'fields' => [
            'contact_title' => ['label' => 'العنوان', 'type' => 'text', 'default' => 'يسعدنا تواصلك معنا'],
            'contact_description' => ['label' => 'الوصف', 'type' => 'textarea', 'default' => 'فريقنا جاهز للإجابة على جميع استفساراتكم المتعلقة بالمنصة، القيم، الأنشطة، أو الدعم الفني.'],
        ],
    ],

];
