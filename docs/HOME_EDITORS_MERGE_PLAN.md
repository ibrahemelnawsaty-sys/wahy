# محرّرات الصفحة الرئيسية «أثيل مكة» — تحليل شامل وخطّة دمج في محرّر واحد موثوق

> وثيقة مرجعيّة للاعتماد والتنفيذ المباشر، متّسقة مع دستور البناء `docs/WAHY_MASTER_BLUEPRINT.md` (§10.1 «المصدر الوحيد للحقيقة»، §3 «تعديل المحتوى دون لمس الكود»، §10.12/§الأمن «تعقيم allowlist purifier + رفض SVG»).

---

## 1) الملخّص التنفيذيّ

**عدد المحرّرات الفعليّة على الصفحة الرئيسية: ستّة** (أربعة ظاهرة + واحد مدمج + واحد ميّت):

1. **«محتوى الصفحة الرئيسية»** — `Admin\HomeContentController` + `lc()` + `landing_content` — **الوحيد المؤثّر خادميًّا على `/`**، لكنّه يغطّي **4 أقسام فقط**.
2. **المحرّر المدمج WYSIWYG** — `landing-editor.js` + `Api\LandingContentController` (FAB للسوبر أدمن على `/`) — **مؤثّر جزئيًّا ومُضلِّل** (يحفظ 89 مفتاحًا، يُقرأ منها 28 فقط).
3. **«بناء الصفحات» (v1)** — `Admin\PageBuilderController` + `page_builder` — **لا يظهر على `/`**.
4. **«محرّر الصفحات (جديد)» (v2)** — `PageBuilderUiController`/`PageManagerController` + `pb_*` — **لا يظهر على `/`** (الأنضج معماريًّا).
5. **`SuperAdminController` landing-page** — `page_builder` slug=home (زرّ في اللوحة) — **لا يظهر على `/`، ويعطب `/home`**.
6. **`Admin\LandingPageController`** (`admin.landing.*`) — **كود ميّت لا يُنفَّذ** (خاسر تصادم المسارات).

**السبب الجذريّ (بجملة):** بعد التراجع `ca5102b`، صارت `PagesController@landing()` تُرجِع `view('landing')` **دائمًا وبلا شرط**، فالمصدر الوحيد المؤثّر على `/` هو `landing.blade` + دالّة `lc()` (جدول `landing_content`)، بينما ثلاثة محرّرات في القائمة تكتب في جداول لا تُقرأ على `/` إطلاقًا، والمحرّر الوحيد الصحيح يغطّي رُبع أقسام الصفحة فقط — فيعدّل المالك ويرى «تمّ الحفظ» ثم لا يتغيّر شيء على `/`.

---

## 2) جدول المحرّرات

| المحرّر | المسار / القائمة | ما يحرّره | أين يُخزَّن | يظهر على `/`؟ | الحكم |
|---|---|---|---|---|---|
| **محتوى الصفحة الرئيسية** (`HomeContentController`) | `admin.home-content.edit` / رابط `admin.blade:196` | 22 حقلًا نصيًّا في 4 أقسام (hero/features/values/contact) | `landing_content` عبر `lc()` | **نعم، خادميًّا** | **يُبقى ويُعتمَد نواةً للمحرّر الموحّد** |
| **المدمج WYSIWYG** (`landing-editor.js` + `Api\LandingContentController`) | FAB على `/` (super_admin) / `api/landing/*` | `innerHTML` + أيقونات + صور لـ89 مفتاحًا + سحب/إفلات | `landing_content` (bulk-update) + قرص `public` | **جزئيًّا** (28 مفتاحًا؛ الباقي للزوّار فقط) | **يُزال/يُقلَّم ويُدمَج في النواة** |
| **بناء الصفحات (v1)** (`PageBuilderController`) | `admin.pages.index` / رابط `admin.blade:216` | صفحات CMS عامّة (about-us…) | `page_builder` (JSON هرميّ) | **لا** | **يُبقى مؤقّتًا لصفحات ثانوية + حظر `slug=home`**، ثم تهجير إلى v2 |
| **محرّر الصفحات الجديد (v2)** (`PageBuilderUiController`/`PageManager`) | `admin.pb.ui.index` / رابط `admin.blade:221` | صفحات كتل آمنة FSE | `pb_*` + علم `settings` | **لا** | **يُبقى لصفحات ثانوية؛ يُستبعَد كأساس للرئيسية** |
| **SuperAdmin landing-page** (`SuperAdminController`) | `admin.landing-page.*` / زرّ `dashboard.blade:229` | كتل مسطّحة لـslug=home | `page_builder` | **لا** (يعطب `/home`) | **يُزال بالكامل** |
| **`Admin\LandingPageController`** | `admin.landing.*` (routes:300‑302) | مخطّط `sections` هرميّ | `page_builder` | **لا (ميّت لا يُنفَّذ)** | **يُزال فورًا** (متحكّم + مسارات + قالب + import) |

---

## 3) التحليل التفصيليّ لكلّ محرّر

### 3.1 «محتوى الصفحة الرئيسية» — `HomeContentController` + `lc` + `landing_content` ✅

أنظف مسار تحرير في المنصّة وأبسطه، والمرشّح الوحيد المطابق للدستور.

- **المسارات** (`routes/web.php:248‑251`، ضمن مجموعة `can:access-admin`): `GET /admin/home-content` → `edit` (`admin.home-content.edit`)؛ `POST /admin/home-content` → `update`.
- **المتحكّم** (`app/Http/Controllers/Admin/HomeContentController.php`، 47 سطرًا):
  - `edit()` (16‑22): يمرّر `sections = config('landing_editable')` و`saved = LandingContent::map()`. النموذج **يُولَّد تلقائيًّا** من الكونفيچ (لا HTML يدويّ لكل حقل).
  - `update()` (24‑46): يمرّ على الكونفيچ قسمًا/حقلًا؛ يتجاهل غير المُرسَل (`! $request->has($key)`)؛ يقصّ `trim`؛ **منطق الحذف-عند-الافتراضيّ**: إن فرغت القيمة أو ساوت `default` ⟶ `LandingContent::where('key',$key)->delete()`، وإلّا `setValue($key,$val,['section'=>'home','type'=>...])`؛ ثمّ `lc_forget()` وإعادة توجيه بنجاح.
- **سِجلّ الحقول (المصدر الواحد)** `config/landing_editable.php`: `قسم => ['label','fields'=>[مفتاح=>['label','type','default']]]`، الأنواع `text|textarea`. **يقود النموذج والافتراضيّات معًا.**
- **دالّة العرض** `lc()` (`app/Helpers/SettingsHelper.php:5‑16`): تقرأ `LandingContent::map()[$key]` أو الافتراضيّ. **عرض خادميّ بحت** (لا سكربت متصفّح). `lc_forget()` (18‑24) تستدعي `flushMap()`.
- **النموذج** `LandingContent`: `setValue()` (48‑61) = `updateOrCreate` بمفتاح `key` + `updated_by` + `flushMap()`؛ `map()` (69‑83) كاش مزدوج (`static $mapCache` داخل الطلب + `Cache::remember('landing_content_map',600,…)`) مغلَّف `try/catch` يُعيد `[]` عند غياب الجدول (تحمّل الأعطال)، استعلام `pluck('value','key')` واحد؛ `flushMap()` (85‑89) يصفّر ويستدعي `Cache::forget`.
- **الجدول** `landing_content` (هجرة `2026_01_09_142048`): `key/value/type/section/order/metadata/version/updated_by` + جدول لقطات `landing_content_versions` (يُملأ من مسار منفصل).

**ماذا يحرّر:** نصوصًا فقط لـ**22 مفتاحًا في 4 أقسام**: hero (4)، features (عنوان+فرعيّ + 9 بطاقات ×2 = 20، مع مفاتيح `feature_1..9_title/desc`)، values (عنوان+فرعيّ فقط)، contact (عنوان+وصف). صفّ لكل مفتاح مخصَّص فقط.

**أثره على `/`: نعم، وهو الوحيد.** لسببين متلازمين: (1) `landing()` تُرجِع `view('landing')` دائمًا؛ (2) `landing.blade` يلفّ الـ22 مفتاحًا بـ`{{ lc('key','default') }}` خادميًّا (hero 291/295/301/308، features 381/385/392‑441، values 452/456، contact 719/723). لا وميض ولا فراغ. **هذا المسار الوحيد الذي يحترم قاعدة §CMS «كل إعداد مُحفَّظ يُستهلَك فعليًّا».**

**فجوة التغطية (أكبر عيب منتَج):** أقسام تحمل `data-editable` لكنّها **غير ملفوفة بـ`lc()` وغير موجودة في الكونفيچ** فنصّها مبثوت ثابت: `teams` (536/540)، `partners` (669/673)، `cta` (862/866/871)، `hero_stats` (313+، محكومة `setting('show_hero_stats')`)، والهيدر/النڤبار، والفوتر، وبطاقات خطوات المنهجية داخل values.

**المخاطر:**
- **ازدواج الافتراضيّ**: القيمة الافتراضيّة مكرّرة في الكونفيچ **و** في الوسيط الثاني لـ`lc()` بالقالب؛ انحرافهما يُحدث فجوة (الحذف يقيس على الكونفيچ، العرض يسقط على القالب).
- **افتراضيّ ديناميكيّ**: `hero_title`/`features_title` في القالب يستخدمان `$siteName` (`'منصة '.$siteName`) بينما الكونفيچ نصّ حرفيّ ⟶ كتابة النصّ المعروض حرفيًّا يوسِّخ الجدول بلا داعٍ (لا عطل).
- **الأمن جيّد بنيويًّا**: العرض عبر `{{ }}` يهرّب كليًّا، والمخزَّن نصّ فقط ⟶ **ليس ناقل XSS**؛ أكثر تحفّظًا ممّا يطلبه الدستور.
- **الكاش متّسق** داخل هذا المسار.

**الحكم: يُبقى — العمود الفقري للمحرّر الموحّد.** نقاط قوّته: مصدر حقيقة واحد مُعلَن، عرض خادميّ بلا وميض، كاش صحيح مُبطَل بالكتابة، تهريب كامل، حذف-عند-الافتراضيّ يبقي الجدول نظيفًا.

---

### 3.2 المحرّر المدمج WYSIWYG — `landing-editor.js` + `Api\LandingContentController` ⚠️

- **المكوّنات:** `public/js/landing-editor.js` (625 سطرًا، مكوّن Alpine `landingEditor()` على `window.landingEditorInstance`)؛ `Api\LandingContentController` (212 سطرًا: `index/update/bulkUpdate/uploadImage/restoreVersion`)؛ مكوّن `element-actions.blade.php` (محميّ `@auth + role==='super_admin'`، مُضمَّن **119 مرّة** في `landing.blade`)؛ تحميل الأصول مشروط بـsuper_admin (148‑153)، `x-data="landingEditor()"` على `<body>` (210)، FAB (886)، لوحة جانبيّة (972)، بطاقات مكوّنات قابلة للسحب (1106‑1141)، تفعيل بـ`?edit=1`.
- **المسارات** (`routes/web.php`): سطر 72 `GET /api/landing/content` → `index` **بلا middleware (عامّ)**؛ 75‑80 ضمن `['auth','role:super_admin']`: `content/update`, `content/bulk-update` (المسار الفعليّ للحفظ), `content/upload-image`, `content/restore/{versionId}`, `content/snapshot` → **`PagesController@landingSnapshot`**.
- **تدفّق الحفظ:** `contentEditable=true` على كل `[data-editable]` ⟶ مستمع `input` يخزّن `el.innerHTML` في `changes[key]` ⟶ `bulk-update` ⟶ `LandingContent::setValue` ⟶ `location.reload()`.

**ماذا يحرّر:** `innerHTML` لكل `data-editable`، أيقونات، صور (قرص `public` تحت `landing-images/`) — يكتب في **نفس مخزن المحرّر الموثوق** (`landing_content`).

**أثره على `/`: جزئيّ، والأغلبيّة الساحقة تُفقَد:**
- **89 مفتاح `data-editable`** قابل للكتابة، **28 فقط** ملفوف بـ`lc()` ⟶ **61 مفتاحًا (68%) يُكتَب ولا يُقرَأ**، يختفي بصمت بعد إعادة التحميل.
- الأقسام الميتة كاملةً: CTA، benefits (`benefit_1..4_*`)، المنهجية (`flow_1..5_*` = 20 مفتاحًا)، الفِرق (`team_1..3_*`، 12)، الشركاء، روابط التنقّل (`nav_link_1..6`)، الشعار (`logo_text/logo_icon`)، الإحصاءات (`stat_*`)، زرّا الدخول/التسجيل.
- **السحب والإفلات وهميّ 100%**: `getComponentTemplate` يولّد مفاتيح `new_hero_<timestamp>_*` لا وجود لها في القالب ⟶ تختفي عند التحميل.
- **رفع الصور/الاسترجاع**: `hero_image`/`site_logo` تُعرَض من مسار مثبَّت (لا `lc`) ⟶ يُفقَد.
- **حتى نطاقه العامل مكسور**: يحفظ `innerHTML` (`<b>`…) لكنّ `lc()` يُصيَّر بـ`{{ }}` ⟶ الوسوم تُعرَض نصًّا حرفيًّا مهروبًا. «الثراء» مكسور بنيويًّا.

**التناقض الحرِج:** بعد أن يحفظ المالك (مُصادَق) مفتاحًا ميّتًا ويُعيد التحميل، **يختفي تعديله من عرضه هو** (القالب لا يقرؤه، وسكربت الارتداد `applyContent` محصور في `@guest` — 1238‑1262) — الزائر وحده يراه (عبر `textContent`). محرّر يحفظ بلا أثرٍ متّسق.

**المخاطر:**
- **انتهاك مباشر لقاعدة الدستور** «لا إعدادات ميتة»: 61 حقلًا + السحب/الإفلات ميتة، مع رسالة «✅ تمّ الحفظ» مضلِّلة.
- **`restoreVersion` هدّام**: `truncate()` ثم إعادة إنشاء من JSON **بلا `DB::transaction`** — فشل جزئيّ يُفقِد كلّ المحتوى.
- **`uploadImage` رابط مكسور**: `asset('storage/data/'.$path)` بينما القرص `public` جذره غير قياسيّ (يجب `Storage::disk('public')->url()`)؛ + الخادم `max:2048` بينما JS يسمح 5MB ⟶ صور 2‑5MB تُرفَض بعد الرفع.
- **`GET /api/landing/content` عامّ غير مصادَق** (سطح إضافيّ؛ الخطر منخفض).
- **تعقيم regex لا purifier**: `PageContentScanner::hasViolations` (blocklist) بدل allowlist purifier الذي يفرضه الدستور؛ `index` يُعيد `value` خامًا — ناقل XSS مخزَّن إن استهلكه قالب بـ`{!! !!}`.

**السياق (التراجع 5804ef8):** `5804ef8` = Revert لـ`41e1a15` («تعميم المحرّر الموثوق + حذف القديم»)، فأعاد إحياء هذا المحرّر وقلّص محرّر النموذج إلى 4 أقسام. العنقود حيّ ومفعّل الآن على `/` للسوبر أدمن بكل فجواته.

**الحكم: يُزال (لا يُدمج) ويُستبدَل بتعميم محرّر النموذج.** نطاقه العامل مكرّر 100% مع النواة، ونطاقه الفريد غير وظيفيّ، و«الثراء» يتعارض مع `{{ lc() }}` ومع التعقيم بـpurifier.

---

### 3.3 المحرّرات القديمة على `page_builder` (v1 + landing-page + LandingPageController) ❌/☠️

الثلاثة تشترك في جدول `page_builder` (موديل `App\Models\PageBuilder`)، وكلّها معزولة عن `/` بنيويًّا لأنّ `landing()` لا تستشير `page_builder` بعد `ca5102b`.

**(أ) `Admin\PageBuilderController` (v1، `admin.pages.*`, routes:284‑291):** CRUD كامل لصفوف `page_builder` بمخطّط JSON هرميّ (`sections→grid→columns→blocks`). مربوط بالقائمة (`admin.blade:216`). **لا يظهر على `/`**؛ يُخدَّم عبر `showPage()` (`/pages/{slug}`) بشرط `servableBySlug`. **خطر split‑brain**: إنشاء `slug='home'` هنا يظهر على `/home` (المسار `home.custom`) بينما `/` يظلّ `landing.blade`. الأمن: يمرّ على `PageContentScanner::scan()` (رفض regex لا تعقيم إيجابيّ). زحام قوالب ميتة حوله (create-simple/visual/backup…). **الحكم: يُبقى مؤقّتًا لصفحات ثانوية + حظر `slug='home'`، ثم تهجير إلى v2.**

**(ب) `SuperAdminController` landing-page (`admin.landing-page.*`, routes:454‑461؛ الدوالّ 1051‑1396):** يكتب صفّ `page_builder` slug=home بـ`json_data` = **مصفوفة كتل مسطّحة** (مخطّط مختلف عن #أ رغم نفس العمود = تضارب مخطّطات). **حيّ عبر زرّ اللوحة** `dashboard.blade:229` (غير مدرَج في الـsidebar). **لا يظهر على `/`، لكن يعطب `/home`**. `importCurrentLanding()` (1270‑1285) → `parseLandingPageToBlocks()` **لقطة نصّيّة مشفّرة يدويًّا بائتة** (إحصاءات وهميّة `500+/50k+/2k+`) لا تقرأ `landing.blade` — «استيراد» مضلِّل هو سبب حادثة الصفحة البيضاء. الأمن الأضعف: لا `PageContentScanner` على `updateLandingContent/addLandingBlock`. **الحكم: يُزال بالكامل** (الدوالّ + المسارات 454‑461 + القالب + زرّ dashboard:229 + توجيه `super-admin.landing-page` سطر 485).

**(ج) `Admin\LandingPageController` (`admin.landing.*`, routes:300‑302):** **ميّت بالتصادم** — يسجّل نفس (method+URI) الذي يسجّله #ب لاحقًا (300 مقابل 454، 301/455، 302/456)، وLaravel يُبقي الأخير ⟶ المُخدَّم فعليًّا SuperAdminController، فقالب `admin/landing-page.blade.php` لا يُبلَغ أبدًا (موثّق في تعليق routes:294‑299). مخطّطه (`sections` هرميّ، `defaultLandingPageDraft`) يناقض مخطّط #ب حتى لو حيَّ. **الحكم: يُزال فورًا** (المتحكّم + مسارات 300‑302 + القالب + `use` في routes:7).

---

### 3.4 محرّر الصفحات v2 — `App\PageBuilder` + `PageManagerController` + `PageResolver` + `pb_*` ❌ (لكن الأنضج)

منظومة CMS كاملة مبنيّة بعناية («شجرة كتل آمنة تُصيَّر عبر مكوّنات Blade، لا HTML خام»):
- **`BlockRegistry`**: قائمة سماح لـ**8 أنواع** (`hero/richtext/image/button/features/columns/cta/spacer`)؛ أي نوع خارجها يُتجاهَل (أساس منع الحقن).
- **`BlockTree::prepare()`**: يُسقِط المجهول بصمت، يطبّع، يرقّي غير مدمِّر. **`BlockValidator::validate()`**: يرفض بأخطاء صريحة + يمرّر على `PageContentScanner::scan()` (تحقّق XSS مزدوج).
- **`PageService`**: `savePage/publishPage/restorePageRevision` داخل `DB::transaction(...,3)` مع لقطة قبل كل تعديل.
- **`PageResolver::resolve($slug,$locale)`**: علم ميزة `pb_v2_enabled_slugs`؛ يُرجِع المنشور فقط إن كان الـslug في العلم، وإلّا `null` (ارتداد آمن).
- **`PageDesign`** (رموز مُعقَّمة regex/قائمة سماح)، **`SlugGuard`** (يحجز `home` + مسارات GET)، **`MediaService`** (**يرفض SVG** امتثالًا للدستور)، النماذج `pb_pages/pb_template_parts/pb_page_revisions/…`.
- **المتحكّمات**: `PageBuilderUiController` (index/create/edit)، `PageManagerController` (store/update/publish/restore/… مع **قفل تفاؤليّ** `expected_updated_at` رفض 409، و`preview` عبر المُصيّر الموثوق). المسارات `admin/pb/*` تحت `can:access-admin`.
- **القوالب** `pb/*`: `document.blade` (FSE: هيدر+جسم+فوتر)، `renderer.blade` (تصيير عبر `BlockRegistry::view`)، `blocks/*` تُهرّب بـ`{{ }}` وتستخدم `safe_url`؛ `richtext.blade` وحده يسمح HTML عبر `safe_html(normalize_message_html(...))`.

**أثره على `/`: لا (قاطعًا).** `landing()` لا تستدعي `PageResolver` إطلاقًا؛ `resolve` يُستدعى في `showPage`/`showPageAlt`/`home` فقط ⟶ أثر v2 يظهر على `/pages/{slug}`, `/page/{slug}`, `/home` وليس `/`. `SlugGuard` يحجز `home` أصلًا.

**المخاطر:** الهجرة `2026_08_01_100000_create_page_builder_v2_tables` غير مُطبَّقة على الإنتاج (**يتطلّب migrate**، أي ربط لـ`/` قبلها = 500)؛ `richtext` يعقّم بـ`safe_html` لا purifier (مقبول للثانويّة، يجب ترقيته لو صار أساس `/`)؛ `goLive('home')` تشويش إدراكيّ (يمسّ `/home` فقط).

**الحكم: يُبقى كما هو لصفحات `/pages/{slug}` الثانويّة، ويُستبعَد كأساس لمحرّر الرئيسية.** أنضج وأأمن بنية في المنصّة (قائمة سماح، تحقّق XSS مزدوج، لقطات، قفل تفاؤليّ، رفض SVG، فصل FSE)؛ لكنّ كتله الثمانية العامّة **لا تُعيد إنتاج** تصميم `landing.blade` المخصَّص (هيرو فيديو، واتساب نابض، خطوات منهجية)، واعتماده يعني هدم الرئيسية — وهو ما رفضه المالك. يُستعار منه **المفاهيم** (اللقطات، التحقّق، التعقيم بقائمة سماح) لا نموذج الكتل.

---

## 4) انتهاكات الدستور

الدستور §10.1 يجعل جدول المحتوى **المصدر الوحيد للحقيقة**، و§3 (سطر 302) يشترط التعديل من اللوحة دون لمس الكود، والقاعدة الحاكمة: «يجب أن تُستهلَك فعلياً كل إعداد مُحفَّظ (لا إعدادات ميتة يحرّرها الأدمن بلا أثر)». الانتهاكات القائمة:

1. **محرّرات بلا أثر (الخرق الأصرح لـ§10.1):**
   - محرّران dead-end (`page_builder` v1 عبر «بناء الصفحات» + `pb_*` v2) معروضان في القائمة يوهمان تحرير `/` بلا أثر.
   - المحرّر المدمج يحفظ **61 مفتاحًا** في `landing_content` بلا مستهلِك خادميّ (`lc()` غائبة) + سحب/إفلات وهميّ + خصائص لا تُحفَظ.
   - `SuperAdminController` landing-page بلا أثر على `/` بعد `ca5102b`، مع «import-current» هدّام مضلِّل.
   - `Admin\LandingPageController` كود ميّت لا يُنفَّذ أصلًا.

2. **تكرار المسارات / نظامان CMS متوازيان:** ثلاثة مسارات تحرير للصفحة نفسها (اثنان بلا أثر)؛ تصادم `admin.landing.*` مع `admin.landing-page.*` (نفس URI مسجَّل مرّتين)؛ ازدواج تخزين ثلاثيّ (`landing_content` + `page_builder` + `pb_*`)؛ ازدواج قراءة على `/pages/{slug}` (v2 ثم ارتداد v1). يخالف مبدأ «لا نظامَي CMS متوازيَين».

3. **`/api` عامّ:** `GET /api/landing/content` بلا middleware (سطح إضافيّ بلا داعٍ).

4. **`safe_html`/regex بدل purifier + عدم رفض SVG:** `Api\LandingContentController` و`PageContentScanner` يعتمدان blocklist regex قابل للتجاوز (§10.12 سطر 6722 يفرض allowlist purifier)؛ `uploadImage` (`image|max:2048`) يقبل SVG (§الأمن سطر 485 يفرض الرفض).

5. **الدستور نفسه قديم في نقطة:** §10.10/§10.9 (سطر 6690/5979) ما زالا يصفان النموذج القديم («`landing()` يعرض page_builder slug=home إن نشِط») بينما الكود صار غير مشروط عمدًا (بطلب المالك) — بند يجب تصحيحه في الدستور ضمن الخطّة.

---

## 5) خطّة الدمج في محرّر واحد موثوق

### القرار المعماريّ الحاكم
**مصدر حقيقة واحد للجذر `/` = `landing_content` (عبر `lc()`) + `settings` (عبر `setting()`)، مُدارٌ من واجهة واحدة هي «محتوى الصفحة الرئيسية».** يُعمَّم سِجلّ الحقول `config/landing_editable.php` ليغطّي **كل** مفتاح `data-editable` في `landing.blade`، ويُلَفّ كل عنصر مقابل بـ`lc()`، فيصير كلّ حقلٍ محرَّرٍ مُستهلَكًا خادميًّا — مطابقةً لـ§10.1. v2 يبقى محرّك الصفحات الثانويّة فقط.

### القرارات الفرعيّة المبرَّرة

**(أ) المحرّرات الميتة/المكرّرة تُزال من القائمة والمسارات:**
- `Admin\LandingPageController` (`admin.landing.*`) + قالبه + مسارات 300‑302 + `use` routes:7 — **حذف** (ميّت بالتصادم).
- `SuperAdminController` landing-page — **حذف** الدوالّ 1051‑1396 + مسارات 454‑461 + القالب `super-admin/landing-page.blade.php` + زرّ `dashboard.blade:229` + توجيه 485.
- المحرّر المدمج WYSIWYG — **حذف** `public/js/landing-editor.js` + `css/landing-edit-mode.css` + `Api\LandingContentController` + `<x-element-actions/>` + FAB/editor-panel من `landing.blade` + مسارات `api/landing` الخمسة + سكربت `@guest` (1238‑1262). (تُطوى اللقطات داخل مسار حفظ محرّر النموذج ضمن `DB::transaction`.)
- «بناء الصفحات» (v1) — **يُبقى مؤقّتًا** لكن **يُحظَر `slug='home'`** في `store/update` (إغلاق فجوة `/home`)، وتُوضَّح تسميته أنّه للصفحات الثانوية.

**(ب) قرار v2:** **يُبقى** (الأنضج، القيمة الأعلى) لصفحات `/pages/{slug}` حصرًا، **معزولًا عن `/`**؛ يُمنَع `goLive/enable` للـslug `home` صراحةً، ويُوحَّد `/pages/{slug}` ليختار v2 **أو** v1 لا بالتراكب، تمهيدًا لإطفاء `page_builder` القديم لاحقًا. لا يُدمَج محتوى الرئيسية فيه (كتله العامّة لا تُعيد إنتاج التصميم المخصَّص).

**(ج) لفّ بقيّة حقول `landing.blade` بـ`lc()`:** توسيع الكونفيچ ليشمل — الهيدر (`logo_text/logo_icon`, `nav_link_1..6`, `login_btn_text`, `register_btn_text`)، المنهجية (`flow_1..5_title/desc/example/number`)، الفِرق (`teams_title/subtitle`, `team_1..3_*`)، الفوائد (`benefits_title`, `benefit_1..4_*`)، الشركاء (`partners_title/subtitle`)، CTA (`cta_title/subtitle/button_text`)، الإحصاءات (`stat_*`)، الفوتر. ثم لفّ كل عنصر مقابل بـ`{{ lc('key','default') }}`. **حسم الافتراضيّ في مكان واحد** (الكونفيچ) لسدّ فجوة الانحراف و`$siteName` (يقرأ القالب `lc('key')` والافتراضيّ يُشتقّ من الكونفيچ عبر مُساعد).

**(د) لقطات/تراجع:** يُدمج منطق `landing_content_versions` (`createSnapshot`) في مسار `HomeContentController@update` داخل `DB::transaction` (لقطة قبل كل حفظ)، مع مسار «تراجع» ذرّيّ يستبدل ضمن معاملة (لا `truncate` بلا معاملة كما في `restoreVersion` القديم).

**(هـ) رفع الوسائط:** إعادة استخدام `App\PageBuilder\MediaService` (يرفض SVG، يكتب `pb_media` على القرص `public`، يبني الرابط عبر `Storage::disk('public')->url()` — لا `asset('storage/data/...')` المكسور) لحقول الشعار/الصور، مع توحيد حدّ الحجم بين الخادم والواجهة.

**(و) تحصين XSS:** حقول النصّ تبقى مهروبة بـ`{{ lc() }}` (لا HTML). أي حقل يحتاج تنسيقًا ثريًّا يمرّ على **HTMLPurifier allowlist** (§10.12) لا `safe_html`/regex، مع **رفض SVG** وأنواع المحتوى النشط في الرفع (§الأمن).

**(ز) تصحيح الدستور:** تحديث §10.9/§10.10 ليعكس أنّ `landing()` صار غير مشروط (يتجاهل `PageResolver` لـ`slug=home`)، وأنّ محرّر الرئيسية الرسميّ هو `landing_content`/`lc()`.

### خارطة التنفيذ المُرحَّلة (دفعات + معايير قبول)

| الدفعة | العمل | معيار القبول |
|---|---|---|
| **0 — تجميد آمن** | حظر `slug='home'` في PageBuilderController (v1) و`goLive/enable('home')` في v2 | لا يمكن إنشاء رئيسيّة موازية على `/home`؛ `route:list` نظيف |
| **1 — إزالة الميّت** | حذف `Admin\LandingPageController` + مسارات 300‑302 + `use` routes:7 + قالبه | `php artisan route:list` بلا تصادم؛ لا استدعاء يتيم |
| **2 — إزالة SuperAdmin landing-page** | حذف الدوالّ 1051‑1396 + مسارات 454‑461 + القالب + زرّ dashboard:229 + توجيه 485 | زرّ اللوحة يختفي؛ لا مسار يكتب `page_builder` slug=home |
| **3 — تعميم `lc()`** | توسيع `config/landing_editable.php` بكل الأقسام + لفّ عناصر `landing.blade` بـ`lc()` + حسم الافتراضيّ في الكونفيچ | كل `data-editable` مغطّى؛ تعديل أي قسم يظهر خادميًّا للمُصادَق والزائر معًا |
| **4 — إزالة المدمج WYSIWYG** | حذف JS/CSS + `Api\LandingContentController` + مسارات api/landing + FAB/panel + `@guest` fallback + `<x-element-actions/>` | لا `/api/landing`؛ لا سكربت تحرير؛ الصفحة تُصيَّر خادميًّا بالكامل |
| **5 — لقطات/تراجع + وسائط** | دمج `createSnapshot` في `update` ضمن `DB::transaction` + تراجع ذرّيّ + ربط `MediaService` للشعار/الصور | لقطة قبل كل حفظ؛ تراجع لا يُتلِف عند الفشل؛ روابط صور سليمة، SVG مرفوض |
| **6 — تعقيم + دستور** | HTMLPurifier allowlist لأي حقل ثريّ + رفض SVG + تحديث §10.9/§10.10 + توثيق نطاق v1/v2 كصفحات ثانوية | فحص أمنيّ يمرّ؛ الدستور متّسق مع الكود |

### المخاطر وتخفيفها
- **تصادم مسارات:** التحقّق بـ`php artisan route:list` بعد كل حذف؛ إزالة `use` اليتيم في `routes/web.php` لمنع أخطاء التحميل.
- **الكاش:** أي كتابة تستدعي `flushMap()`/`lc_forget()`؛ إزالة كاش الزوّار القديم إن بقي؛ الانتباه لكاش `Cache::remember(...,600)` (تعديلات قد تتأخّر 10 دقائق دون إبطال).
- **تراجع سابق (`5804ef8` = Revert لـ`41e1a15`):** هذه الخطّة هي **إعادة تنفيذ `41e1a15` بإتقان**؛ التنفيذ **تدريجيّ بدفعات** (لا دفعة واحدة كبيرة) مع اختبار `/` بعد كل دفعة لتفادي حادثة «الصفحة البيضاء» التي سبّبها إنشاء صفّ `page_builder` فارغ منشور.
- **الهجرة على الإنتاج:** v2 يتطلّب `migrate` قبل أي اعتماد؛ يُبقى معزولًا عن `/` فلا يُدخِل خطر 500 على الجذر.
- **ازدواج الحقيقة أثناء الانتقال:** حتى تكتمل الدفعة 4، المحرّران (النواة + المدمج) يكتبان نفس الجدول — يُنجَز حذف المدمج مباشرة بعد تعميم `lc()` (الدفعة 3) لتقليص نافذة التعارض.

---

### الملفّات المرجعيّة (مطلقة)
- `C:\Users\b.maher\Downloads\wahy (2)\app\Http\Controllers\PagesController.php` (landing 21‑28، home 110، showPage 77)
- `C:\Users\b.maher\Downloads\wahy (2)\app\Http\Controllers\Admin\HomeContentController.php`
- `C:\Users\b.maher\Downloads\wahy (2)\config\landing_editable.php` (4 أقسام فقط)
- `C:\Users\b.maher\Downloads\wahy (2)\app\Helpers\SettingsHelper.php` (lc 5‑16، lc_forget 18‑24)
- `C:\Users\b.maher\Downloads\wahy (2)\app\Models\LandingContent.php` (map/flushMap/setValue 48‑89، createSnapshot 106‑122)
- `C:\Users\b.maher\Downloads\wahy (2)\resources\views\landing.blade.php` (lc 291‑723؛ data-editable بلا lc: الهيدر 216‑271، flow 461‑524، teams/benefits 531‑660، cta 857‑876؛ FAB 881‑1229؛ @guest 1238‑1262)
- `C:\Users\b.maher\Downloads\wahy (2)\public\js\landing-editor.js` (saveChanges 125، duplicateSection 561، dragStart 248، updateProperty 583)
- `C:\Users\b.maher\Downloads\wahy (2)\app\Http\Controllers\Api\LandingContentController.php` (bulkUpdate 95، uploadImage 145، restoreVersion 177)
- `C:\Users\b.maher\Downloads\wahy (2)\resources\views\components\element-actions.blade.php`
- `C:\Users\b.maher\Downloads\wahy (2)\app\Http\Controllers\Admin\PageBuilderController.php` + قالبات `admin/pages/*`
- `C:\Users\b.maher\Downloads\wahy (2)\app\Http\Controllers\Admin\LandingPageController.php` (ميّت)
- `C:\Users\b.maher\Downloads\wahy (2)\app\Http\Controllers\SuperAdminController.php` (1051‑1396)
- `C:\Users\b.maher\Downloads\wahy (2)\app\Http\Controllers\Admin\PageBuilderUiController.php` + `app\PageBuilder\{BlockRegistry,BlockTree,BlockValidator,PageService,PageResolver,PageDesign,SlugGuard,MediaService}.php`
- `C:\Users\b.maher\Downloads\wahy (2)\database\migrations\2026_08_01_100000_create_page_builder_v2_tables.php`
- `C:\Users\b.maher\Downloads\wahy (2)\resources\views\pb\{document,renderer,blocks\richtext}.blade.php`
- `C:\Users\b.maher\Downloads\wahy (2)\routes\web.php` (landing GET؛ home-content 248‑251؛ api/landing 72‑80؛ pages 284‑291؛ تصادم landing 294‑302 مقابل landing-page 454‑461؛ توجيه 485؛ pb 202‑234)
- `C:\Users\b.maher\Downloads\wahy (2)\resources\views\layouts\admin.blade.php` (196/216/221 الروابط الثلاثة)
- `C:\Users\b.maher\Downloads\wahy (2)\resources\views\admin\dashboard.blade.php` (229)
- `C:\Users\b.maher\Downloads\wahy (2)\app\Models\PageBuilder.php` (servableBySlug 126‑131)
- `C:\Users\b.maher\Downloads\wahy (2)\app\Support\PageContentScanner.php`
- `C:\Users\b.maher\Downloads\wahy (2)\docs\WAHY_MASTER_BLUEPRINT.md` (§10.1:6082؛ §10.12:6711؛ الأمن 266/485؛ 6690 قديم يصف page_builder على `/`)