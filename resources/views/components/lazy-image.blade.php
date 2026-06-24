{{--
    Lazy-loaded image — يستخدم browser-native lazy loading.

    Variables:
      $src         — URL الصورة الحقيقية
      $alt         — نص بديل (إجباري للـ a11y)
      $width/$height — أبعاد ثابتة (مهمة للـ CLS)
      $class       — classes إضافية
      $eager       — true لو فوق الـ fold (تجاوز lazy loading)
      $placeholder() — SVG صغير inline لمنع layout shift
--}}
@if ($eager)
    {{-- فوق الـ fold — تحميل فوري --}}
    <img
        src="{{ $src }}"
        alt="{{ $alt }}"
        @if ($width)width="{{ $width }}"@endif
        @if ($height)height="{{ $height }}"@endif
        class="{{ $class }}"
        decoding="async"
        fetchpriority="high"
    />
@else
    {{-- تحت الـ fold — lazy loading --}}
    <img
        src="{{ $placeholder() }}"
        data-src="{{ $src }}"
        alt="{{ $alt }}"
        @if ($width)width="{{ $width }}"@endif
        @if ($height)height="{{ $height }}"@endif
        class="{{ $class }}"
        loading="lazy"
        decoding="async"
        onload="if(this.dataset.src){this.src=this.dataset.src;this.removeAttribute('data-src');}"
    />
@endif
