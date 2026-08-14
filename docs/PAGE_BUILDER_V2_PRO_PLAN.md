# خطّة تطوير محرّر الصفحات v2 (App\PageBuilder) إلى محرّر احترافيّ شبيه بالووردبريس

> وثيقة معماريّة تنفيذيّة. الهدف: معالجة شكاوى المالك الثلاث (هيدر/فوتر لكلّ صفحة، معاينة حيّة، ثراء كالووردبريس) عبر **توسيع** معمار v2 القائم لا إعادة كتابته، مع احترام دستور `docs/WAHY_MASTER_BLUEPRINT.md` (رندرة عبر مكوّنات Blade فقط، قائمة سماح، لا HTML خام، نموذج المناطق FSE).

---

## 1) الوضع الحاليّ باختصار (نقاط قوّة v2 + الفجوات الثلاث الحاكمة)

### نقاط القوّة (أساسٌ صلب نبني فوقه — لا نهدمه)

- **خطّ رندرة آمن بالتصميم.** كلّ صفحة تُصيَّر عبر `resources/views/pb/document.blade.php` الذي يؤلّف ثلاث مناطق FSE (هيدر/جسم/فوتر، الأسطر 38-48)، وكلّ منطقة تمرّ عبر `BlockTree::prepare()` ثمّ `pb.renderer`. `BlockRegistry::all()` هي **قائمة السماح الوحيدة** (`app/PageBuilder/BlockRegistry.php:16-28`)؛ أيّ نوع خارجها يُسقَط ولا يُصبّ خاماً (`BlockTree.php:23-24`، `renderer.blade.php:5-9`). لا HTML خام إلّا كتلة `richtext` عبر `safe_html(normalize_message_html())` (`pb/blocks/richtext.blade.php:3`).
- **دفاع بالعمق عند الحفظ.** `BlockValidator::validate()` (بنية + `PageContentScanner` لِـXSS) يُشغَّل على `store`/`update`/`publish`/`updatePart` (`PageManagerController.php:48,74,86,224`)، و`SlugGuard` يمنع تظليل مسارات النظام (`:42,67`).
- **نموذج بيانات جاهز أكثر ممّا هو مُستغَلّ.** الهجرة `2026_08_01_100000_create_page_builder_v2_tables.php` تُنشئ 5 جداول: `pb_template_parts`, `pb_template_part_revisions`, `pb_pages`, `pb_page_revisions`, `pb_media`. وجداول `pb_pages` تحمل بالفعل `header_part_id`/`footer_part_id` (FK اختياريّان، `:51-52`) و`og_image` (`:55`) و`published_at` (`:56`).
- **ترحيل تدريجيّ آمن الارتداد.** `PageResolver` يخدم v2 فقط للمسارات المُفعَّلة عبر علم `pb_v2_enabled_slugs` والمنشورة (`PageResolver.php:49-60`)؛ غير ذلك يرتدّ للنظام القديم بلا شاشة بيضاء.
- **مكتبة وسائط حقيقيّة مُحصَّنة.** رفض SVG في ثلاث طبقات + إعادة فحص MIME الحقيقيّ + أسماء عشوائيّة + `alt` إلزاميّ.
- **محرّر خفيف بلا React** مُولَّد بالكامل من `BlockRegistry::schema()` (`pb-editor.js`) — إضافة كتلة بحقول قياسيّة لا تتطلّب لمس JS.

### الفجوات الثلاث الحاكمة (شكاوى المالك حرفيّاً)

**الفجوة ١ — الهيدر/الفوتر مشتركان عبر كلّ الصفحات الثانوية (لا تخصيص لكلّ صفحة).**
النموذج **يدعم التخصيص بالفعل**: `document.blade.php:13-14` يقرأ `$page->header ?: TemplatePart::activeFor('header',$locale)`، و`Page::header()/footer()` معرَّفتان (`Models/Page.php:39-47`)، و`validated()` يقبل الحقلين (`PageManagerController.php:241-242,251-252`)، و`PageService::savePage` يحفظهما (`PageService.php:29-30,48-49`). لكنّ الميزة **ميتة** لأنّ:
- `boot()` لا يرسل `header_part_id`/`footer_part_id` ولا قائمة الأجزاء المتاحة (`PageBuilderUiController.php:42-51`).
- `saveBody()` لا يضمّهما في الحمولة (`pb-editor.js:281-285`).
- `activePart` يُرجِع **جزءاً فعّالاً واحداً فقط** لكلّ (kind+locale) عبر `->first()` (`TemplatePart.php:41-44`, `PageManagerController.php:170-189`)، ولا توجد نقاط لسرد/إنشاء/تعيين أجزاء متعدّدة (`routes/web.php:201-224`).

النتيجة: تحرير الهيدر في أيّ صفحة يعدّل الصفّ العالميّ الوحيد فيظهر على كلّ الصفحات.

**الفجوة ٢ — لا معاينة حيّة بصريّة (WYSIWYG).**
«الكانفس» في المحرّر ليس رندرة بل **قائمة بطاقات مجرّدة**: `renderCanvas`→`renderList` ترسم `pb-card` بأيقونة + تسمية + ملخّص نصّيّ مُجرَّد من الوسوم (`summaryOf` يقطع كلّ الوسوم، `pb-editor.js:88-137`). الرندرة الحقيقيّة الوحيدة عبر زرّ «👁 معاينة» الذي يفتح **مودالاً منفصلاً** يُملأ مرّةً واحدة عبر `POST /admin/pb/preview` (`pb-editor.js:326-336`)، وهو: مودال، عند الطلب فقط، لا يتحدّث تلقائيّاً، وبلا هيدر/فوتر/تصميم حقيقيّ (`pb/preview.blade.php:15` يلفّ الكتل في `.pb-page-body` فقط). فعليّاً: نموذج بيانات CMS، لا محرّر بصريّ.

**الفجوة ٣ — الثراء أقلّ بكثير من الووردبريس.**
- **مكتبة كتل نحيفة**: 8 أنواع فقط (`BlockRegistry.php:18-28`) مقابل ~25+ في الووردبريس.
- **المستند العامّ يشحن صفر JavaScript** (`document.blade.php:37-49`): لا خطّ أصول لكلّ كتلة → فئة الكتل التفاعليّة (أكورديون/تبويبات/سلايدر/عدّاد) **غير قابلة للبناء أصلاً**.
- **لا تضمينات (Embeds)**: `PageContentScanner:62` يحجب `iframe/object/embed/svg` — لا فيديو ولا خرائط.
- **لا تحكّم نمط لكلّ كتلة**: الكتل تحمل حقول محتوى فقط؛ التصميم عالميّ (`PageDesign` = 6 رموز في `Setting` واحد، والمودال يقول صراحةً «تُطبَّق على كلّ الصفحات» `editor.blade.php:78`).
- **لا سحب-وإفلات، لا تراجع/إعادة، لا حفظ تلقائيّ**، ومُنتقي الابن عبر `window.prompt()` رقميّ (`pb-editor.js:140-146`).
- **لا أنماط/كتل قابلة لإعادة الاستخدام**، ولا فصل مسودّة/منشور (الحفظ يدفع التغيير حيّاً فوراً على الصفحة المنشورة).

---

## 2) الرؤية: محرّر صفحات احترافيّ شبيه بالووردبريس (المبادئ)

1. **التوسيع لا إعادة الكتابة.** كلّ ميزة جديدة تدخل عبر نقاط التمديد القائمة: مدخل في `BlockRegistry::all()`+`schema()`، مكوّن Blade موثوق، حقل محرّر في `pb-editor.js`، عمود/جدول جديد عند الحاجة. لا مسار رندرة ثانٍ، ولا «كتلة HTML خام».
2. **الأمن ثابتٌ غير قابل للتفاوض.** قائمة السماح تبقى المصدر الوحيد؛ الرندرة عبر Blade؛ التحقّق عبر `BlockValidator`+`PageContentScanner`؛ التصميم عبر مُعقِّم صارم؛ التضمينات تُبنى خادميّاً من قائمة مضيفين مسموحين — لا iframe خام أبداً.
3. **المعاينة الحيّة = رندرة خادميّة في iframe مُحدَّثة تلقائيّاً**، لا `innerHTML` لِـHTML المستخدم في DOM لوحة الأدمن (التزامٌ بالبند الدستوريّ للـXSS من طرف العميل). هذا يحقّق WYSIWYG **والأمن معاً** لأنّه يعيد استخدام مسار الرندرة الموثوق نفسه.
4. **النموذج FSE محفوظ ومُوسَّع.** هيدر/جسم/فوتر مناطق مستقلّة؛ «هيدر لكلّ صفحة» يتحقّق بإسناد `header_part_id`/`footer_part_id` وتعدّد الأجزاء المُسمّاة — داخل النموذج لا خارجه.
5. **تدرّجيّة قابلة للشحن.** كلّ دفعة تُسلّم قيمةً مستقلّة وقابلة للارتداد؛ الدفعة الأولى تُطفئ الألم فوراً (معاينة حيّة + هيدر/فوتر لكلّ صفحة).
6. **فصل المسودّة عن المنشور** كي يُحرّر المالك صفحةً حيّةً بأمان ويراجع قبل الدفع (كما في الووردبريس).

---

## 3) الحلول للشكاوى الثلاث — بتفصيل معماريّ

### 3.أ) تحرير الهيدر/الفوتر لكلّ صفحة (الشكوى ١)

النموذج جاهز؛ ما ينقص هو **طبقة الأجزاء المتعدّدة + الإسناد + الإخفاء**. الخطوات:

**١) نقاط أجزاء القالب (backend).** توسيع `PageManagerController` + `routes/web.php:201-224`:
- `GET /admin/pb/parts?kind=header&locale=ar` → سرد كلّ أجزاء النوع (لا `->first()` فقط). يتطلّب أمراً في المتحكّم يعيد `TemplatePart::kind($kind)->where('locale',$locale)->get(['id','name','kind','is_active'])`.
- `POST /admin/pb/parts` → إنشاء جزء مُسمّى جديد (`name`, `kind`, `locale`, `blocks:[]`). يستعمل `TemplatePart::create` القائم (نمط `activePart` `:175-184`).
- `POST /admin/pb/parts/{part}/set-default` → رفع `is_active` لهذا الجزء وخفضها عن أشقائه (نفس kind+locale) — يعرّف «الافتراضيّ العالميّ» الذي يرتدّ إليه `activeFor`.
- `POST /admin/pb/parts/{part}/restore/{revision}` → **`restorePart`** المفقود (المرآة الدقيقة لـ`restore` الصفحات `:95-100`؛ اللقطات تُلتقط أصلاً في `PageService::snapshotPart` `:112-121` لكنّ مسار الاسترجاع غائب `routes:217`).

**٢) خيار «بلا هيدر/فوتر» لصفحة بعينها.** هجرة صغيرة تضيف عمودين لـ`pb_pages`:
```
$table->boolean('hide_header')->default(false);
$table->boolean('hide_footer')->default(false);
```
ثمّ في `document.blade.php:39,45` نضيف الشرط: `@if(!$page->hide_header && !empty($headerBlocks))`. (لا نستعمل `null` للدلالة على «بلا» لأنّ `null` تعني «الافتراضيّ العالميّ» — نفصل الدلالتين صراحةً.)

**٣) حمولة الإقلاع والحفظ (frontend).**
- `PageBuilderUiController::boot()` (`:42-51`) يضمّ: `page.header_part_id`, `page.footer_part_id`, `page.hide_header`, `page.hide_footer`، وقائمة `parts` مُجمَّعة حسب النوع للُّغة الحاليّة.
- `pb-editor.js` `saveBody()` (`:281-285`) يرسل `header_part_id`/`footer_part_id`/`hide_header`/`hide_footer`. الخادم يقبلها أصلاً (`validated()` `:241-242,251-252`) — فقط أضِف `hide_header`/`hide_footer` إلى قواعد التحقّق و`savePage` (`:29-33`).

**٤) واجهة الاختيار.** في لوحة «إعدادات الصفحة» (`editor.blade.php:38-47`) نضيف حقلين:
- `<select>` للهيدر: «الافتراضيّ العامّ» (`null`) / «بلا هيدر» (`hide_header=true`) / [الأجزاء المُسمّاة]. ومثله للفوتر.
- زرّ «+ هيدر جديد» يفتح الجزء في تبويب التحرير (المحرّر يدعم تحرير الأجزاء أصلاً عبر `switchRegion` `:425-437`).

> **النتيجة:** كلّ صفحة تختار هيدرها/فوترها (افتراضيّ/بلا/متغيّر مُسمّى)، مع تراجع للأجزاء — كلّه داخل نموذج FSE، بلا HTML خام.

### 3.ب) المعاينة الحيّة (الشكوى ٢)

المفتاح: نقطة `POST /admin/pb/preview` **موجودة وآمنة** (`PageManagerController::preview` `:209-214` عبر `BlockTree::prepare`→`pb.preview`). نحوّلها من مودال عند الطلب إلى **لوح حيّ مُثبَّت يتحدّث تلقائيّاً**، ونجعله يعرض هيكل الصفحة الكامل.

**١) لوح معاينة مُثبَّت (dock).** نحوّل شبكة المحرّر `pb-grid` (`editor.blade.php:120`، حاليّاً `210px 1fr 300px`) إلى إتاحة عمود/لوح معاينة (`iframe`) بجانب الكانفس أو بديلاً عنه بتبويب «بصريّ/مُنظَّم». نستبقي `#pbPreviewFrame`.

**٢) تحديث تلقائيّ مُهذَّب (debounced).** في `pb-editor.js` `renderAll()` (`:253`) نستدعي `schedulePreview()` بمؤقّت 300–500ms يعيد `POST /preview` ويحدّث `srcdoc`. كلّ `onChange`/`addBlock`/`moveBlock`/`deleteBlock` يمرّ عبر `renderAll` أصلاً، فيتحدّث اللوح فوراً.

**٣) معاينة بالهيكل الكامل (تُطابق المنشور).** نضيف عرض `resources/views/pb/preview-doc.blade.php` (أو نُوسِّع `preview.blade.php:15`) يؤلّف هيدر+جسم+فوتر مثل `document.blade.php:38-48` من الحالة العاملة المُرسَلة، مع حقن `PageDesign::cssVars()` (يجري أصلاً عبر `base-styles`). نُوسِّع `preview()` (`:209-214`) ليقبل: كتل الجسم + `header_part_id`/`footer_part_id` المختارَين (أو كتلهما العاملة) + المنطقة قيد التحرير. هكذا يرى المالك الصفحة **بهيدرها وفوترها وتصميمها الحقيقيّ**.

**٤) تزامن التحديد (نقر↔كتلة).** في `renderer.blade.php` نضيف علماً اختياريّاً `$editable` يلفّ كلّ كتلة بـ`<div data-pb-path="{{ $i }}">` (في مسار المعاينة فقط، لا المستند العامّ). ثمّ `postMessage` من الـiframe عند النقر → المحرّر يختار الكتلة ويُبرز بطاقتها؛ والعكس (تحديد بطاقة → تمرير للكتلة في المعاينة). هذا يحقّق تجربة Gutenberg دون `innerHTML` لِـHTML المستخدم.

**٥) (لاحقاً) تحرير نصّيّ في المكان.** كتل النصّ (عنوان/فقرة) تُحرَّر مباشرةً على الكانفس عبر `contenteditable` مع مزامنة للحالة ثمّ رندرة خادميّة — تبقى الرندرة النهائيّة خادميّة.

> **الأمن:** المعاينة تبقى رندرةً خادميّة موثوقة في iframe (مسار `BlockTree::prepare`+`renderer` نفسه) — لا نحقن HTML المستخدم في DOM الأدمن. هذا هو الحلّ الذي يرضي الدستور والـWYSIWYG معاً.

### 3.ج) مكتبة كتل غنيّة + محرّر شبيه بالووردبريس (الشكوى ٣)

**١) توسيع المكتبة عبر الوصفة القائمة (4 خطوات).** لكلّ كتلة: مدخل في `all()` + مدخل في `schema()` + `pb/blocks/X.blade.php` + CSS في `base-styles.blade.php`. (تفصيل القائمة في القسم 4.)

**٢) خطّ أصول لكلّ كتلة (البنية الأهمّ للكتل التفاعليّة).** حاليّاً المستند يشحن صفر JS. نضيف:
- مفتاح اختياريّ `assets` في `BlockRegistry::all()`: `['css' => '...', 'js' => '...']` أو اسم وحدة.
- في `document.blade.php` نجمع أنواع الكتل المستعملة فعلاً ونحقن **فقط** أصول تلك الكتل: ملفّ `public/js/pb-runtime.js` صغير يُهيّئ ذاتيّاً عبر `data-*` (أكورديون/تبويبات/سلايدر/عدّاد)، يُحمَّل شرطيّاً. لا JS مؤلّف من المستخدم — منطق ثابت مُدقَّق فقط.

**٣) أنواع حقول محرّر جديدة.** نُوسِّع `fieldControl` (`pb-editor.js:149-203`) بـ: `color` (منتقي لون)، `toggle` (منطقيّ)، `range` (منزلق)، `align` (أزرار محاذاة)، `gallery` (متعدّد صور فوق `openMedia`)، `link` (نصّ+رابط)، `icon` (منتقي أيقونات). هذا يفتح عشرات الكتل والتحكّم بالنمط.

**٤) تحكّم النمط لكلّ كتلة.** فضاء خصائص موحّد `props._style` (لون/خلفيّة/تباعد/محاذاة/عرض) يُعقَّم خادميّاً بنفس صرامة `PageDesign::sanitize` (`:44-66`) ويُصدَّر كـ**متغيّرات CSS مضمّنة أو أصناف من قائمة سماح** — لا سلاسل CSS حرّة أبداً. كلّ كتلة Blade تقرأ `_style` عبر مُساعد `pb_block_style($block)` يُخرج `style="--x:..."` مُعقَّمة.

**٥) مُنتقي كتل احترافيّ (Inserter).** استبدال `renderPalette` (`:75-85`) بواجهة فيها: بحث + تصنيفات (نضيف `category` لكلّ مدخل schema) + مصغّرات. واستبدال `openTypeMenu` بـ`prompt()` (`:140-146`) بنفس واجهة المُنتقي (مع السماح بالحاويات داخل الحاويات).

**٦) تحقّق مخطّط الخصائص.** توسيع `BlockValidator` (`:15-26`) ليستشير `BlockRegistry::schema()` ويتحقّق من الحقول المطلوبة/الأنواع/قوائم `select`، فيعيد أخطاءً واضحة للمؤلّف (المحرّر يعرض مصفوفة الأخطاء أصلاً).

**٧) أنماط وكتل قابلة لإعادة الاستخدام.** استغلال `kind='generic'` المتاح في الهجرة (`:24`) لتخزين «أنماط» جاهزة (أقسام مُعدّة مسبقاً) وكتل مزامَنة، مع واجهة إدراج «مكتبة الأنماط».

**٨) تحصين `richtext` بمُعقِّم قائمة سماح.** `ezyang/htmlpurifier` **موجود في vendor** (`composer.lock:1004`) لكن غير موصول. نضيف `App\PageBuilder\HtmlPurify` (غلاف تهيئة صارمة) ونستبدل `safe_html` في `richtext.blade.php:3` على المخرَج (مع إبقاء `PageContentScanner` بوّابةَ الإدخال) — تنفيذٌ للبند الدستوريّ R9/§10.12.

**٩) راحات المحرّر.** زرّ استنساخ كتلة (استنساخ عميق للـJSON)، تراجع/إعادة (مكدّس تاريخ في `pb-editor.js`)، حفظ تلقائيّ مُهذَّب، معاينة أجهزة (سطح/لوح/جوّال) عبر عرض iframe.

---

## 4) مكتبة الكتل المقترحة (قائمة + أولويّة + جهد عبر نمط BlockRegistry)

المعايير: **S** = مدخل سجلّ + مدخل schema + Blade + CSS بحقول قائمة (لا JS). **M** = يتطلّب نوع حقل محرّر جديد أو معرض صور. **L** = يتطلّب نظاماً فرعيّاً جديداً (خطّ أصول JS / iframe مسموح / نقطة خادميّة).

| الكتلة | النوع الجديد | التصنيف | الأولويّة | الجهد | ملاحظة تنفيذيّة |
|---|---|---|---|---|---|
| عنوان (Heading) | `heading` | نصّ | حرجة | S | حقل `level` مُقيَّد h1–h6 (يحرسه `PageContentScanner:28-31` أصلاً) |
| فقرة / قائمة (List) | `list` | نصّ | حرجة | S | `repeater` عناصر نصّيّة |
| اقتباس / اقتباس بارز | `quote`,`pullquote` | نصّ | عالية | S | نصّ + عزو |
| فاصل / خطّ (Divider) | `separator` | تخطيط | عالية | S | نمط select (خطّ/نقاط/مسافة) |
| مجموعة أزرار (Buttons) | `buttons` | تفاعل | عالية | S | `repeater` من {text,link,style} فوق نمط `button` |
| قائمة أيقونات (Icon list) | `iconlist` | نصّ | عالية | S | `repeater` {icon,text} |
| شهادة عميل (Testimonial) | `testimonial` | تسويق | عالية | S | صورة+اسم+نصّ عبر حقول قائمة |
| جدول أسعار (Pricing) | `pricing` | تسويق | عالية | S | `repeater` باقات |
| روابط اجتماعيّة (Social) | `social` | هيدر/فوتر | عالية | S | `repeater` {network,url} + `safe_url` |
| جدول (Table) | `table` | محتوى | متوسّطة | S | `repeater` صفوف (نصّ فقط) |
| بطاقة/غلاف (Cover) | `cover` | تخطيط | عالية | M | صورة خلفيّة + طبقة تعتيم (حقل لون/شفافيّة) |
| مجموعة/قسم (Group/Section) | `section` | حاوية | عالية | M | حاوية بخلفيّة/تباعد/عرض (تُمكّن التخطيطات المركّبة؛ حاليّاً `columns` الحاوية الوحيدة) |
| معرض صور (Gallery) | `gallery` | وسائط | عالية | M | حقل `gallery` متعدّد الصور جديد |
| إحصاءات/عدّادات (Stats) | `stats` | تسويق | متوسّطة | M | حقل رقم + وحدة (عدّاد متحرّك = L لاحقاً) |
| تقييم نجوم (Rating) | `rating` | تسويق | منخفضة | M | حقل `range` |
| **أكورديون / أسئلة شائعة** | `accordion` | تفاعل | عالية | L | يتطلّب خطّ أصول JS |
| **تبويبات (Tabs)** | `tabs` | تفاعل | عالية | L | خطّ أصول JS |
| **سلايدر/كاروسيل** | `carousel` | تفاعل | متوسّطة | L | خطّ أصول JS |
| **عدّاد تنازليّ (Countdown)** | `countdown` | تفاعل | منخفضة | L | خطّ أصول JS |
| **تضمين فيديو (YouTube/Vimeo)** | `video` | تضمين | عالية | L | iframe مبنيّ خادميّاً من `host+id` مسموح |
| **خريطة (Maps)** | `map` | تضمين | متوسّطة | L | iframe مسموح (إحداثيّات/عنوان مُعقَّم) |
| **نموذج تواصل (Form)** | `form` | تفاعل | متوسّطة | L | نقطة خادميّة + حماية CSRF/سبام |
| **تنقّل/قائمة (Navigation)** | `nav` | هيدر | عالية | L/M | كتلة قائمة + مدير قوائم (يجعل الهيدر مفيداً) |

> **دفعة «مكسب سريع» للمكتبة:** الصفّ العلويّ من كتل **S** (heading/list/quote/separator/buttons/iconlist/testimonial/pricing/social/table) يُضاف كلّه دون لمس JS المحرّر — أسرع طريق لمضاعفة المكتبة ثلاث مرّات.

---

## 5) خارطة تنفيذ مُرحَّلة

> كلّ دفعة: العمل + معيار القبول + المخاطر. الدفعة 1 تُطفئ الألم فوراً.

### الدفعة 1 — المكسب السريع: معاينة حيّة + هيدر/فوتر لكلّ صفحة ⭐
**العمل:**
- **معاينة حيّة مُثبَّتة**: تحويل مودال المعاينة إلى لوح مُثبَّت + تحديث تلقائيّ مُهذَّب من `renderAll` (`pb-editor.js:253,326-336`)؛ توسيع `preview()` وعرض `preview-doc` ليعرض الهيدر/الفوتر/التصميم الكامل (`PageManagerController.php:209-214`, `pb/preview.blade.php`).
- **هيدر/فوتر لكلّ صفحة (نهاية-لنهاية)**: نقاط سرد/إنشاء/تعيين-افتراضيّ/`restorePart` للأجزاء؛ هجرة `hide_header`/`hide_footer`؛ `boot()` يشحن ids+قائمة الأجزاء؛ `<select>` في إعدادات الصفحة؛ `saveBody` يرسل الحقول (`document.blade.php:39,45`، `PageBuilderUiController.php:42-51`، `pb-editor.js:281-285`).
- **راحات فوريّة**: استبدال `prompt()` بواجهة المُنتقي؛ زرّ استنساخ كتلة؛ بحث/تصنيفات في اللوحة.

**معيار القبول:** المالك يرى الصفحة المُصيَّرة تتحدّث لحظيّاً وهو يضيف كتلاً، بهيدرها/فوترها/تصميمها الحقيقيّ؛ ويُسند لصفحة معيّنة هيدراً مختلفاً أو «بلا هيدر» دون تأثّر بقيّة الصفحات؛ ويسترجع نسخة أقدم من الهيدر.

**المخاطر:** تحديث المعاينة المتكرّر قد يُثقل الشبكة → تهذيب زمنيّ + إلغاء الطلب السابق. عمودا الإخفاء يتطلّبان `migrate` على الإنتاج. تصادم جلستين على نفس الجزء → يحرسه القفل التفاؤليّ الموجود (`:62-65`) بعد مدّه للأجزاء.

### الدفعة 2 — مكتبة كتل غنيّة (كتل S) + مُنتقي احترافيّ + تحقّق المخطّط
**العمل:** إضافة كتل الصفّ S العشر (القسم 4)؛ إضافة `category` لكلّ schema؛ مُنتقي ببحث/تصنيفات/مصغّرات؛ توسيع `BlockValidator` لتحقّق حقول `schema()`.
**معيار القبول:** ≥18 نوع كتلة، مُنتقٍ قابل للبحث ومُصنَّف، وأخطاء تأليف واضحة عند حقل مفقود.
**المخاطر:** تضخّم `base-styles`؛ يُخفَّف بتقسيم CSS الكتل منطقيّاً. لا هجرة.

### الدفعة 3 — تحكّم النمط لكلّ كتلة + رموز تصميم لكلّ صفحة
**العمل:** أنواع حقول `color/toggle/range/align/width` في `fieldControl`؛ فضاء `_style` مُعقَّم خادميّاً (نمط `PageDesign::sanitize`) يُصدَّر كمتغيّرات CSS/أصناف؛ رموز تصميم لكلّ صفحة (عمود `pb_pages.design_tokens` JSON يُدمج فوق العالميّ)؛ اشتقاق الوضع الليليّ من الرموز (`base-styles.blade.php:31-35`)؛ كتل `cover`/`section`/`gallery`.
**معيار القبول:** المالك يغيّر لون/تباعد/محاذاة كتلة واحدة؛ ويعطي صفحةً لوحة ألوان مختلفة؛ والألوان المخصّصة تبقى في الوضع الليليّ.
**المخاطر:** حقن CSS إن مُرِّرت قيم حرّة → **كلّ قيمة نمط تمرّ بالمُعقِّم**، لا استثناء. هجرة عمود `design_tokens`.

### الدفعة 4 — الكتل التفاعليّة + خطّ أصول لكلّ كتلة + التضمينات
**العمل:** مفتاح `assets` في السجلّ + `pb-runtime.js` يُحقَن شرطيّاً في `document.blade.php`؛ كتل `accordion/tabs/carousel/countdown/stats(عدّاد)`؛ كتلة `video`/`map`/`nav` عبر iframe مبنيّ خادميّاً من مضيفين مسموحين.
**معيار القبول:** أكورديون/تبويبات تعمل على الصفحة المنشورة؛ تضمين فيديو YouTube يظهر دون أيّ iframe خام مُخزَّن؛ الأصول تُحمَّل فقط حين تُستعمل الكتلة.
**المخاطر:** أكبر إضافة بنيويّة → أطلقها خلف علم؛ قائمة مضيفي التضمين قائمة سماح صارمة؛ راجع CSP للصفحات العامّة.

### الدفعة 5 — إدارة محتوى احترافيّة (مسودّة/نشر، جدولة، إصدارات، وسائط، SEO)
**العمل:** فصل مسودّة عاملة عن منشور (عمود `published_blocks` أو `draft_blocks` في `pb_pages`) فيُحرَّر الحيّ بأمان؛ نشر مجدوَل (قبول `published_at` مستقبليّ + شرط `published_at <= now()` في `PageResolver::resolve` `:55-60`)؛ استنساخ صفحة + إلغاء نشر؛ توسيع اللقطة لتشمل المستند كاملاً (`PageService::snapshotPage` `:101-110` يخزّن الجسم فقط) وواجهة تصفّح/استرجاع الإصدارات (`boot` `:42-51`، المسار `routes:212` موجود)؛ رابط معاينة مسودّة موقَّع؛ مكتبة وسائط (حذف/تعديل alt/بحث/تحميل-المزيد/`srcset`+`width/height`/`og_image`/سحب-إفلات)؛ إثراء `<head>` (canonical/og:description/twitter/hreflang من `translation_group`)؛ وصل `HTMLPurifier` لِـ`richtext` وإعادة صياغة `PageContentScanner` كقائمة سماح.
**معيار القبول:** تعديل صفحة حيّة دون ظهوره للجمهور حتى إعادة النشر؛ جدولة صفحة لوقت مستقبليّ؛ استرجاع نسخة يعيد المستند كاملاً؛ تحرير alt بعد الرفع؛ صور بأبعاد جوهريّة (لا انزياح تخطيط).
**المخاطر:** فصل المسودّة يمسّ نموذج البيانات → هجرة + مسار قراءة مزدوج (المُصيِّر يقرأ `published_blocks`، المحرّر يقرأ العامل). هجرات متعدّدة.

### الدفعة 6 — الأنماط والكتل القابلة لإعادة الاستخدام + الأنماط العالميّة
**العمل:** مكتبة أنماط/أقسام جاهزة وكتل مزامَنة (فوق `kind='generic'` أو جدول `pb_patterns` جديد)؛ لوحة أنماط عالميّة (لوحة ألوان مسمّاة + مقياس تباعد + مقياس خطوط)؛ خطوط ذاتيّة الاستضافة بديلاً عن `fonts.googleapis.com` (`base-styles.blade.php:3-4`).
**معيار القبول:** إدراج «قسم تسعير» جاهز بنقرة؛ كتلة مزامَنة تتحدّث في كلّ مواضعها؛ لا اعتماد خارجيّ للخطوط.
**المخاطر:** إدارة تزامن الكتل المُعاد استعمالها؛ يُطلَق أخيراً بعد استقرار المكتبة.

---

## 6) قيود الدستور والأمن التي يجب احترامها

1. **رندرة عبر قائمة سماح فقط.** كلّ كتلة جديدة = مدخل في `BlockRegistry::all()` + مكوّن Blade موثوق. أيّ نوع خارج السجلّ يُسقَط (`BlockTree.php:23-24`, `renderer.blade.php:5-9`). **ممنوع** إدخال «كتلة HTML خام» أو أيّ مسار يصبّ ترميزاً مُخزَّناً.
2. **لا HTML خام إلّا `richtext`.** كلّ القيم تُهرَّب عبر `{{ }}`/`safe_url`. الاستثناء الوحيد `richtext` يمرّ عبر `safe_html+normalize_message_html` (`richtext.blade.php:3`). و§10.12/R9 (`blueprint:483,6735,8194`) تُلزم **استبدال `safe_html` (قائمة حظر regex قابلة للتجاوز) بمُعقِّم قائمة سماح** (`HTMLPurifier` الموجود في `composer.lock:1004`) على الإدخال والإخراج — **لا تُوسَّع قائمة حظر `safe_html` أبداً**.
3. **بوّابة الإدخال ثابتة.** `BlockValidator` (`+PageContentScanner`) يبقى على `store/update/publish/updatePart` (`PageManagerController.php:48,74,86,224`). `PageContentScanner` يرفض المخطّطات الخطرة والوسوم (`script/iframe/svg/object/embed/form...` `:62`) ومعالجات `on*=` (`:67`).
4. **التصميم لكلّ صفحة/كتلة يمرّ بالمُعقِّم نفسه.** أيّ نمط (`_style`/`design_tokens`) يُعقَّم بصرامة `PageDesign::sanitize` (`:44-66`: hex فقط/خطّ من قائمة سماح/أرقام مقيَّدة) ويُصدَّر كمتغيّرات CSS أو أصناف — **لا سلاسل CSS حرّة**.
5. **التضمينات خادميّة فقط.** كتلة `video`/`map` تبني iframe من `host+id` **مُعقَّم مقابل قائمة مضيفين مسموحين** — لا iframe مُلصَق من المستخدم (يرفضه `PageContentScanner:62` أصلاً).
6. **XSS من طرف العميل.** DOM لوحة الأدمن يستعمل `textContent`/`esc()` (`pb-editor.js:23`). المعاينة الحيّة **iframe مُصيَّر خادميّاً** لا `innerHTML` لِـHTML المستخدم. وتُصلَح تهيئة `richtext` عبر `ed.innerHTML` (`pb-editor.js:162`) بمسار مُعقَّم (DOMPurify/textContent).
7. **الرفع.** يبقى رفض SVG/المحتوى النشط + إعادة فحص MIME الحقيقيّ + أسماء عشوائيّة + `alt` إلزاميّ (`MediaService.php:16`, `MediaLibraryController.php:40,44-47`). أنواع الوسائط الجديدة تلتزم بذلك.
8. **الروابط.** كلّ حقل رابط عبر `safe_url()` مقيَّداً بـ`http/https/mailto` (`image.blade.php:5-13`, `button.blade.php:9`).
9. **نموذج FSE محفوظ.** هيدر/جسم/فوتر مناطق مستقلّة؛ «هيدر لكلّ صفحة» عبر إسناد `header_part_id`/`footer_part_id` وتعدّد الأجزاء — **داخل** النموذج.
10. **ترحيل آمن الارتداد.** `SlugGuard` (حجز المسارات) + علم `pb_v2_enabled_slugs` يُبقيان الإطلاق تدريجيّاً وقابل الارتداد. حمولة الإقلاع تبقى عبر `@json` بأعلام HEX (`editor.blade.php:101`)، والحفظ عبر `X-CSRF-TOKEN` (`pb-editor.js:257`) + القفل التفاؤليّ (`PageManagerController.php:62-65`).
11. **تنظيف.** حذف/عزل `public/js/page-builder-pro.js` الميت (غير موصول بأيّ عرض؛ منطق v1 قديم) قبل التوسّع، منعاً لوصل مسار رندرة/حفظ ثانٍ غير مُدقَّق.

---

## 7) الملفّات المرجعيّة (مطلقة)

**نواة v2 (App\PageBuilder):**
- `c:/Users/b.maher/Downloads/wahy (2)/app/PageBuilder/BlockRegistry.php` — قائمة السماح (`all()` :16-28) + مخطّط المحرّر (`schema()` :34-80)
- `c:/Users/b.maher/Downloads/wahy (2)/app/PageBuilder/BlockTree.php` — إسقاط المجهول + الترقية (:12-34)
- `c:/Users/b.maher/Downloads/wahy (2)/app/PageBuilder/BlockValidator.php` — تحقّق الحفظ (:15-26)
- `c:/Users/b.maher/Downloads/wahy (2)/app/PageBuilder/PageService.php` — حفظ/نشر/تراجع + لقطات (:19-121)
- `c:/Users/b.maher/Downloads/wahy (2)/app/PageBuilder/PageResolver.php` — علم الترحيل + resolve (:49-60)
- `c:/Users/b.maher/Downloads/wahy (2)/app/PageBuilder/PageDesign.php` — الرموز العالميّة + التعقيم (:44-109)
- `c:/Users/b.maher/Downloads/wahy (2)/app/PageBuilder/MediaService.php` — الرفع + رفض SVG
- `c:/Users/b.maher/Downloads/wahy (2)/app/PageBuilder/Models/Page.php` — header()/footer() + fillable (:18-64)
- `c:/Users/b.maher/Downloads/wahy (2)/app/PageBuilder/Models/TemplatePart.php` — activeFor ->first() (:41-44)
- `c:/Users/b.maher/Downloads/wahy (2)/app/PageBuilder/Models/{PageRevision,TemplatePartRevision,MediaAsset}.php`

**المتحكّمات:**
- `c:/Users/b.maher/Downloads/wahy (2)/app/Http/Controllers/Admin/PageManagerController.php` — CRUD/نشر/معاينة/أجزاء (:38-231)
- `c:/Users/b.maher/Downloads/wahy (2)/app/Http/Controllers/Admin/PageBuilderUiController.php` — boot() (:37-68)
- `c:/Users/b.maher/Downloads/wahy (2)/app/Http/Controllers/Admin/MediaLibraryController.php`
- `c:/Users/b.maher/Downloads/wahy (2)/app/Support/PageContentScanner.php` — بوّابة XSS للإدخال

**العروض (Blade):**
- `c:/Users/b.maher/Downloads/wahy (2)/resources/views/admin/pb/editor.blade.php` — الشبكة/التبويبات/المودالات (:29,38-47,76-97,120)
- `c:/Users/b.maher/Downloads/wahy (2)/resources/views/admin/pb/index.blade.php`
- `c:/Users/b.maher/Downloads/wahy (2)/resources/views/pb/document.blade.php` — تأليف FSE + ارتداد الهيدر/الفوتر (:13-14,38-48)
- `c:/Users/b.maher/Downloads/wahy (2)/resources/views/pb/renderer.blade.php` — الرندرة الموثوقة (:5-10)
- `c:/Users/b.maher/Downloads/wahy (2)/resources/views/pb/preview.blade.php` — معاينة بلا هيكل (:15)
- `c:/Users/b.maher/Downloads/wahy (2)/resources/views/pb/blocks/*.blade.php` — 8 كتل (richtext:3 = HTML الوحيد)
- `c:/Users/b.maher/Downloads/wahy (2)/resources/views/pb/partials/base-styles.blade.php` — CSS + الرموز + الوضع الليليّ (:6,31-35)

**JS:**
- `c:/Users/b.maher/Downloads/wahy (2)/public/js/pb-editor.js` — المحرّر الحيّ (renderCanvas:129، preview:326، saveBody:281، fieldControl:149، openTypeMenu:140)
- `c:/Users/b.maher/Downloads/wahy (2)/public/js/page-builder-pro.js` — **ميت** (للحذف/العزل)

**البيانات والمسارات:**
- `c:/Users/b.maher/Downloads/wahy (2)/database/migrations/2026_08_01_100000_create_page_builder_v2_tables.php` — الجداول الخمسة (`pb_template_parts`, `pb_template_part_revisions`, `pb_pages` مع `header_part_id`/`footer_part_id`/`og_image`, `pb_page_revisions`, `pb_media`)
- `c:/Users/b.maher/Downloads/wahy (2)/routes/web.php` — مجموعة `admin/pb` (:187-224)

**الدستور:**
- `c:/Users/b.maher/Downloads/wahy (2)/docs/WAHY_MASTER_BLUEPRINT.md` — §10.12/R9/R10 (:483,485,6724-6735,8194-8195): مُعقِّم قائمة السماح، رفض SVG، لا `innerHTML` لِـHTML المستخدم، نموذج FSE.
- `c:/Users/b.maher/Downloads/wahy (2)/composer.lock` — `ezyang/htmlpurifier` مُثبَّت (:1004) وغير موصول (يُوصَل في الدفعة 5).