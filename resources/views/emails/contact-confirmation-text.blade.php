مرحباً {{ $data['full_name'] }},

شكراً لتواصلك مع منصة {{ setting('site_name', 'أثيل مكة') }}. تمّ استلام رسالتك بنجاح
وسيردّ عليك فريقنا في أقرب وقت (وقت الاستجابة المتوقّع: 1-2 يوم عمل).

ملخّص رسالتك:
{{ \Illuminate\Support\Str::limit($data['message'], 200) }}

—
منصة {{ setting('site_name', 'أثيل مكة') }}
{{ config('app.url') }}
