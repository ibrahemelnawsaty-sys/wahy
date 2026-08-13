<?php

/**
 * سِجلّ حقول محتوى الصفحة الرئيسية القابلة للتحرير (المصدر الواحد).
 * يقود: (1) نموذج تحرير الأدمن (يُولَّد تلقائيّاً)، (2) العرض الخادميّ عبر lc('key', الافتراضيّ).
 *
 * البنية: قسم => [ 'label' => عنوان القسم، 'fields' => [ مفتاح => [label, type, default] ] ]
 * الأنواع: text | textarea. الافتراضيّ = النصّ الحاليّ في القالب (يظهر إن لم يُحفَظ تخصيص).
 * الإضافة لاحقاً: أضِف الحقل هنا ثمّ لُفّ العنصر في landing.blade.php بـ{{ lc('key', 'default') }}.
 *
 * ملاحظة: اسم الموقع/وصفه/الشعار/البريد/الهاتف/روابط التواصل تُحرَّر من «الإعدادات العامة»
 * (مصدر واحد)، فلا تُكرَّر هنا كي لا يتضارب مصدران.
 */
return [

    'header' => [
        'label' => 'الترويسة (القائمة العلوية)',
        'fields' => [
            'nav_link_1' => ['label' => 'رابط 1', 'type' => 'text', 'default' => 'الرئيسية'],
            'nav_link_2' => ['label' => 'رابط 2', 'type' => 'text', 'default' => 'المميزات'],
            'nav_link_3' => ['label' => 'رابط 3', 'type' => 'text', 'default' => 'القيم'],
            'nav_link_4' => ['label' => 'رابط 4', 'type' => 'text', 'default' => 'الأنشطة'],
            'nav_link_5' => ['label' => 'رابط 5 (الشركاء)', 'type' => 'text', 'default' => 'الشركاء'],
            'nav_link_6' => ['label' => 'رابط 6', 'type' => 'text', 'default' => 'الدعم'],
            'login_btn_text' => ['label' => 'زرّ تسجيل الدخول', 'type' => 'text', 'default' => 'تسجيل دخول'],
            'register_btn_text' => ['label' => 'زرّ التسجيل', 'type' => 'text', 'default' => 'ابدأ الآن'],
        ],
    ],

    'hero' => [
        'label' => 'الواجهة (الهيرو)',
        'fields' => [
            'hero_title' => ['label' => 'العنوان الرئيسيّ', 'type' => 'textarea', 'default' => 'منصة أثيل مكة.. قيم نبوية يحيى بها الطالب'],
            'hero_description' => ['label' => 'الوصف', 'type' => 'textarea', 'default' => 'من مكة المكرمة .. نبع الهدايات، انطلقت منصتنا تلهم الواردين من الطلاب والمعلمين.. تجمع بين المتعة والفائدة لتبني جيلاً فعالاً متخلقاً بقيم وأخلاق النبوة'],
            'hero_btn_primary' => ['label' => 'زرّ أساسيّ', 'type' => 'text', 'default' => 'ابدأ الآن'],
            'hero_btn_secondary' => ['label' => 'زرّ ثانويّ', 'type' => 'text', 'default' => 'اعرف المزيد'],
            'stat_schools' => ['label' => 'إحصائية 1 — الرقم', 'type' => 'text', 'default' => '500+'],
            'stat_schools_label' => ['label' => 'إحصائية 1 — النصّ', 'type' => 'text', 'default' => 'مدرسة'],
            'stat_students' => ['label' => 'إحصائية 2 — الرقم', 'type' => 'text', 'default' => '50k+'],
            'stat_students_label' => ['label' => 'إحصائية 2 — النصّ', 'type' => 'text', 'default' => 'طالب'],
            'stat_teachers' => ['label' => 'إحصائية 3 — الرقم', 'type' => 'text', 'default' => '2k+'],
            'stat_teachers_label' => ['label' => 'إحصائية 3 — النصّ', 'type' => 'text', 'default' => 'معلم'],
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
        'label' => 'قسم «كيف نبني القيم؟» (المنهجية)',
        'fields' => [
            'values_title' => ['label' => 'عنوان القسم', 'type' => 'text', 'default' => 'كيف نبني القيم؟'],
            'values_subtitle' => ['label' => 'العنوان الفرعيّ', 'type' => 'text', 'default' => 'منهجية متكاملة من القيمة إلى التطبيق العملي'],
            'flow_1_title' => ['label' => 'خطوة 1 — العنوان', 'type' => 'text', 'default' => 'القيمة الكلية'],
            'flow_1_example' => ['label' => 'خطوة 1 — المثال', 'type' => 'text', 'default' => 'الرحمة'],
            'flow_1_desc' => ['label' => 'خطوة 1 — الوصف', 'type' => 'textarea', 'default' => 'اختيار قيمة كلية تندرج تحتها مجموعة من القيم الضمنية'],
            'flow_2_title' => ['label' => 'خطوة 2 — العنوان', 'type' => 'text', 'default' => 'القيمة الضمنية'],
            'flow_2_example' => ['label' => 'خطوة 2 — المثال', 'type' => 'text', 'default' => 'بر الوالدين'],
            'flow_2_desc' => ['label' => 'خطوة 2 — الوصف', 'type' => 'textarea', 'default' => 'حصر القيم الضمنية المتعلقة بالقيمة الكلية وإدراجها في المنظومة'],
            'flow_3_title' => ['label' => 'خطوة 3 — العنوان', 'type' => 'text', 'default' => 'المفاهيم الرئيسية'],
            'flow_3_example' => ['label' => 'خطوة 3 — المثال', 'type' => 'text', 'default' => 'الإحسان إلى الوالدين'],
            'flow_3_desc' => ['label' => 'خطوة 3 — الوصف', 'type' => 'textarea', 'default' => 'استخراج المفاهيم الرئيسية من القيمة الضمنية'],
            'flow_4_title' => ['label' => 'خطوة 4 — العنوان', 'type' => 'text', 'default' => 'المعاني المرتبطة'],
            'flow_4_example' => ['label' => 'خطوة 4 — المثال', 'type' => 'text', 'default' => 'طاعة الوالدين – إكرام الوالدين – الدعاء لهما'],
            'flow_4_desc' => ['label' => 'خطوة 4 — الوصف', 'type' => 'textarea', 'default' => 'تحديد أهم المعاني المتعلقة بالمفهوم'],
            'flow_5_title' => ['label' => 'خطوة 5 — العنوان', 'type' => 'text', 'default' => 'الأنشطة'],
            'flow_5_example' => ['label' => 'خطوة 5 — المثال', 'type' => 'text', 'default' => 'حفل الإحسان'],
            'flow_5_desc' => ['label' => 'خطوة 5 — الوصف', 'type' => 'textarea', 'default' => 'تنفيذ أنشطة ومشاريع لتعزيز المفاهيم والمعاني وتطبيقها'],
        ],
    ],

    'teams' => [
        'label' => 'قسم الفرق والتعلّم التعاونيّ',
        'fields' => [
            'teams_title' => ['label' => 'عنوان القسم', 'type' => 'text', 'default' => 'التعلم التعاوني مع الفرق'],
            'teams_subtitle' => ['label' => 'العنوان الفرعيّ', 'type' => 'text', 'default' => 'نظام فرق ذكي يحفز الطلاب على التعاون والتنافس الإيجابي'],
            'teams_how_title' => ['label' => 'عنوان «كيف يعمل»', 'type' => 'text', 'default' => 'كيف يعمل نظام الفرق؟'],
            'hiw_1_title' => ['label' => 'كيف يعمل 1 — العنوان', 'type' => 'text', 'default' => 'فرق صغيرة'],
            'hiw_1_desc' => ['label' => 'كيف يعمل 1 — الوصف', 'type' => 'textarea', 'default' => 'كل فصل يُقسم إلى فرق من 4-6 طلاب لتحقيق التعاون الفعّال'],
            'hiw_2_title' => ['label' => 'كيف يعمل 2 — العنوان', 'type' => 'text', 'default' => 'نظام نقاط متطور'],
            'hiw_2_desc' => ['label' => 'كيف يعمل 2 — الوصف', 'type' => 'textarea', 'default' => 'كل فريق يكسب نقاط عند إتمام الأنشطة والمهام الجماعية'],
            'hiw_3_title' => ['label' => 'كيف يعمل 3 — العنوان', 'type' => 'text', 'default' => 'جوائز وتحفيز'],
            'hiw_3_desc' => ['label' => 'كيف يعمل 3 — الوصف', 'type' => 'textarea', 'default' => 'الفريق الفائز يحصل على شارات وجوائز تشجيعية'],
            'hiw_4_title' => ['label' => 'كيف يعمل 4 — العنوان', 'type' => 'text', 'default' => 'لوحة صدارة'],
            'hiw_4_desc' => ['label' => 'كيف يعمل 4 — الوصف', 'type' => 'textarea', 'default' => 'متابعة أداء الفرق في الوقت الفعلي وتحديثات مستمرة'],
            'team_1_name' => ['label' => 'فريق 1 — الاسم', 'type' => 'text', 'default' => 'فريق الريادة'],
            'team_1_points' => ['label' => 'فريق 1 — النقاط', 'type' => 'text', 'default' => '2,450 نقطة'],
            'team_2_name' => ['label' => 'فريق 2 — الاسم', 'type' => 'text', 'default' => 'فريق السمو'],
            'team_2_points' => ['label' => 'فريق 2 — النقاط', 'type' => 'text', 'default' => '2,180 نقطة'],
            'team_3_name' => ['label' => 'فريق 3 — الاسم', 'type' => 'text', 'default' => 'فريق المعالي'],
            'team_3_points' => ['label' => 'فريق 3 — النقاط', 'type' => 'text', 'default' => '1,920 نقطة'],
            'benefits_title' => ['label' => 'عنوان «فوائد التعاون»', 'type' => 'text', 'default' => 'فوائد التعلم التعاوني'],
            'benefit_1_title' => ['label' => 'فائدة 1 — العنوان', 'type' => 'text', 'default' => 'تعزيز التعاون'],
            'benefit_1_desc' => ['label' => 'فائدة 1 — الوصف', 'type' => 'textarea', 'default' => 'يتعلم الطلاب العمل معاً وتحقيق الأهداف المشتركة'],
            'benefit_2_title' => ['label' => 'فائدة 2 — العنوان', 'type' => 'text', 'default' => 'تطوير التواصل'],
            'benefit_2_desc' => ['label' => 'فائدة 2 — الوصف', 'type' => 'textarea', 'default' => 'تحسين مهارات التواصل والاستماع للآخرين'],
            'benefit_3_title' => ['label' => 'فائدة 3 — العنوان', 'type' => 'text', 'default' => 'تنمية التفكير'],
            'benefit_3_desc' => ['label' => 'فائدة 3 — الوصف', 'type' => 'textarea', 'default' => 'تبادل الأفكار يساعد على التفكير النقدي والإبداعي'],
            'benefit_4_title' => ['label' => 'فائدة 4 — العنوان', 'type' => 'text', 'default' => 'بناء العلاقات'],
            'benefit_4_desc' => ['label' => 'فائدة 4 — الوصف', 'type' => 'textarea', 'default' => 'تكوين صداقات وعلاقات إيجابية بين الطلاب'],
        ],
    ],

    'partners' => [
        'label' => 'قسم الشركاء',
        'fields' => [
            'partners_title' => ['label' => 'عنوان القسم', 'type' => 'text', 'default' => 'شركاؤنا في النجاح'],
            'partners_subtitle' => ['label' => 'العنوان الفرعيّ', 'type' => 'text', 'default' => 'ثقة أكثر من 500 مدرسة ومؤسسة تعليمية رائدة'],
        ],
    ],

    'contact' => [
        'label' => 'قسم التواصل',
        'fields' => [
            'contact_title' => ['label' => 'العنوان', 'type' => 'text', 'default' => 'يسعدنا تواصلك معنا'],
            'contact_description' => ['label' => 'الوصف', 'type' => 'textarea', 'default' => 'فريقنا جاهز للإجابة على جميع استفساراتكم المتعلقة بالمنصة، القيم، الأنشطة، أو الدعم الفني.'],
            'contact_email_label' => ['label' => 'مسمّى البريد', 'type' => 'text', 'default' => 'البريد الإلكتروني'],
            'contact_phone_label' => ['label' => 'مسمّى الهاتف', 'type' => 'text', 'default' => 'رقم الهاتف'],
            'contact_hours_label' => ['label' => 'مسمّى أوقات العمل', 'type' => 'text', 'default' => 'أوقات العمل'],
            'contact_hours_value' => ['label' => 'قيمة أوقات العمل', 'type' => 'text', 'default' => 'الأحد – الخميس | 8:00 صباحًا – 4:00 مساءً'],
            'contact_social_title' => ['label' => 'عنوان «تابعنا»', 'type' => 'text', 'default' => 'تابعنا على'],
        ],
    ],

    'cta' => [
        'label' => 'شريط الدعوة للتسجيل',
        'fields' => [
            'cta_title' => ['label' => 'العنوان', 'type' => 'text', 'default' => 'جاهز للانضمام؟'],
            'cta_subtitle' => ['label' => 'العنوان الفرعيّ', 'type' => 'text', 'default' => 'ابدأ رحلتك اليوم'],
            'cta_button_text' => ['label' => 'زرّ الدعوة', 'type' => 'text', 'default' => 'ابدأ مجاناً'],
        ],
    ],

    'footer' => [
        'label' => 'التذييل (الفوتر)',
        'fields' => [
            'footer_quick_title' => ['label' => 'عنوان «روابط سريعة»', 'type' => 'text', 'default' => 'روابط سريعة'],
            'footer_link_home' => ['label' => 'رابط سريع — الرئيسية', 'type' => 'text', 'default' => 'الرئيسية'],
            'footer_link_features' => ['label' => 'رابط سريع — المميزات', 'type' => 'text', 'default' => 'المميزات'],
            'footer_link_values' => ['label' => 'رابط سريع — القيم', 'type' => 'text', 'default' => 'القيم'],
            'footer_link_activities' => ['label' => 'رابط سريع — الأنشطة', 'type' => 'text', 'default' => 'الأنشطة'],
            'footer_link_partners' => ['label' => 'رابط سريع — الشركاء', 'type' => 'text', 'default' => 'الشركاء'],
            'footer_accounts_title' => ['label' => 'عنوان «الحسابات»', 'type' => 'text', 'default' => 'الحسابات'],
            'footer_login' => ['label' => 'الحسابات — تسجيل الدخول', 'type' => 'text', 'default' => 'تسجيل الدخول'],
            'footer_register' => ['label' => 'الحسابات — إنشاء حساب', 'type' => 'text', 'default' => 'إنشاء حساب'],
            'footer_legal_title' => ['label' => 'عنوان «روابط قانونية»', 'type' => 'text', 'default' => 'روابط قانونية'],
            'footer_privacy' => ['label' => 'قانونيّ — الخصوصية', 'type' => 'text', 'default' => 'سياسة الخصوصية'],
            'footer_terms' => ['label' => 'قانونيّ — الشروط', 'type' => 'text', 'default' => 'الشروط والأحكام'],
            'footer_contact_title' => ['label' => 'عنوان «تواصل معنا»', 'type' => 'text', 'default' => 'تواصل معنا'],
        ],
    ],

];
