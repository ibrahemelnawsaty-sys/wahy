{{--
    اتّجاه RTL + خطّ الموقع لكلّ قوالب البريد (مصدر واحد). يُضمَّن في <head> **بعد** أيّ <style> للقالب
    كي يغلب عليه عند تساوي الخصوصيّة. selectors العناصر فقط (لا تغلب الأصناف ولا الـinline).
    لا text-align على td/th: يُلغي align="center" لأنّ سمات العرض أضعف من أيّ قاعدة CSS.
    العملاء التي تُسقط <style>/dir على html (Gmail بحسابات غير جوجل…) يغطّيها الـinline في الغلاف (انظر emails.partials.rtl-open).
--}}
<link href="{{ email_font_url() }}" rel="stylesheet">
<style>
    html, body { direction: rtl; text-align: right; }
    body, table, td, th, p, div, span, a, h1, h2, h3, h4, h5, h6, li, strong, em, small, button {
        font-family: {!! email_font_stack() !!};
    }
    table, td, th { direction: rtl; }
</style>
