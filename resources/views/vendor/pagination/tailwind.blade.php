{{-- Wahy Pagination — قالب ترقيم فاخر مكتفٍ ذاتياً (لا يعتمد على Tailwind/Bootstrap) يعمل في كل الأدوار،
     متجاوب مع الوضع الليلي (html[data-theme="dark"]) ويستعمل لون الهوية var(--color-primary/secondary). --}}
@once
<style>
    .wahy-pg {
        display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between;
        gap: 14px 18px; margin: 22px 0; font-family: inherit;
    }
    .wahy-pg-info { font-size: 13px; color: #64748b; font-weight: 500; }
    .wahy-pg-info b { color: #0f172a; font-weight: 700; }
    .wahy-pg-list {
        display: inline-flex; flex-wrap: wrap; align-items: center; gap: 6px; direction: rtl;
    }
    .wahy-pg-link, .wahy-pg-dots, .wahy-pg-nav {
        display: inline-flex; align-items: center; justify-content: center;
        min-width: 42px; height: 42px; padding: 0 12px;
        border-radius: 12px; font-size: 14px; font-weight: 700;
        text-decoration: none; user-select: none;
        border: 1.5px solid rgba(15, 23, 42, 0.10);
        background: #ffffff; color: #334155;
        transition: transform .16s ease, background .2s ease, border-color .2s ease, box-shadow .2s ease, color .2s ease;
    }
    .wahy-pg-nav { padding: 0 10px; }
    .wahy-pg-nav svg { width: 18px; height: 18px; }
    /* روابط قابلة للنقر: تفاعل ناعم */
    a.wahy-pg-link:hover, a.wahy-pg-nav:hover {
        border-color: var(--color-primary, #667eea);
        color: var(--color-primary, #667eea);
        background: color-mix(in srgb, var(--color-primary, #667eea) 8%, #ffffff);
        transform: translateY(-2px);
        box-shadow: 0 8px 18px rgba(102, 126, 234, 0.18);
    }
    a.wahy-pg-link:active, a.wahy-pg-nav:active { transform: translateY(0); }
    /* الصفحة الحالية: متدرّج الهوية الفاخر */
    .wahy-pg-active {
        background: linear-gradient(135deg, var(--color-primary, #667eea), var(--color-secondary, #764ba2)) !important;
        border-color: transparent !important; color: #fff !important;
        box-shadow: 0 10px 22px rgba(102, 126, 234, 0.38);
        cursor: default; transform: none;
    }
    /* المعطّل (بلا صفحات سابقة/تالية) */
    .wahy-pg-disabled {
        opacity: .45; cursor: not-allowed; box-shadow: none;
    }
    .wahy-pg-dots { border: none; background: transparent; min-width: 26px; padding: 0 2px; color: #94a3b8; }

    /* نسخة الجوال المبسّطة (سابق/تالي فقط) */
    .wahy-pg-mobile { display: none; width: 100%; justify-content: space-between; gap: 10px; }
    .wahy-pg-mobile .wahy-pg-link { flex: 1; }

    /* ===== الوضع الليلي ===== */
    html[data-theme="dark"] .wahy-pg-info { color: #94a3b8; }
    html[data-theme="dark"] .wahy-pg-info b { color: #f1f5f9; }
    html[data-theme="dark"] .wahy-pg-link,
    html[data-theme="dark"] .wahy-pg-nav {
        background: rgba(255, 255, 255, 0.05); border-color: rgba(255, 255, 255, 0.12); color: #cbd5e1;
    }
    html[data-theme="dark"] a.wahy-pg-link:hover,
    html[data-theme="dark"] a.wahy-pg-nav:hover {
        background: rgba(255, 255, 255, 0.12); color: #fff;
        border-color: var(--color-primary, #93c5fd);
        box-shadow: 0 8px 18px rgba(0, 0, 0, 0.4);
    }
    html[data-theme="dark"] .wahy-pg-dots { color: #64748b; }

    @media (max-width: 640px) {
        .wahy-pg { justify-content: center; }
        .wahy-pg-desktop { display: none; }
        .wahy-pg-mobile { display: flex; }
        .wahy-pg-info { order: 2; width: 100%; text-align: center; }
    }
</style>
@endonce

@if ($paginator->hasPages())
    <nav role="navigation" aria-label="التنقل بين الصفحات" class="wahy-pg" dir="rtl">
        {{-- نص النتائج --}}
        <p class="wahy-pg-info">
            عرض
            @if ($paginator->firstItem())
                <b>{{ $paginator->firstItem() }}</b> إلى <b>{{ $paginator->lastItem() }}</b>
            @else
                {{ $paginator->count() }}
            @endif
            من أصل <b>{{ $paginator->total() }}</b> نتيجة
        </p>

        {{-- الجوال: سابق/تالي فقط --}}
        <div class="wahy-pg-mobile">
            @if ($paginator->onFirstPage())
                <span class="wahy-pg-link wahy-pg-disabled">السابق</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="wahy-pg-link" rel="prev">السابق</a>
            @endif
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="wahy-pg-link" rel="next">التالي</a>
            @else
                <span class="wahy-pg-link wahy-pg-disabled">التالي</span>
            @endif
        </div>

        {{-- الديسكتوب: أرقام الصفحات --}}
        <span class="wahy-pg-list wahy-pg-desktop">
            {{-- السابق --}}
            @if ($paginator->onFirstPage())
                <span class="wahy-pg-nav wahy-pg-disabled" aria-hidden="true" aria-label="السابق">
                    <svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="wahy-pg-nav" aria-label="السابق">
                    <svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg>
                </a>
            @endif

            {{-- الأرقام --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="wahy-pg-dots" aria-disabled="true">{{ $element }}</span>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="wahy-pg-link wahy-pg-active" aria-current="page">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="wahy-pg-link" aria-label="الذهاب للصفحة {{ $page }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- التالي --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="wahy-pg-nav" aria-label="التالي">
                    <svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                </a>
            @else
                <span class="wahy-pg-nav wahy-pg-disabled" aria-hidden="true" aria-label="التالي">
                    <svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                </span>
            @endif
        </span>
    </nav>
@endif
