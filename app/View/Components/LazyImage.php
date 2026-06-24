<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * Lazy-loaded Image Component مع responsive sources.
 *
 * استخدام في Blade:
 *   <x-lazy-image
 *       :src="$user->avatar_url"
 *       alt="صورة الطالب"
 *       width="200"
 *       height="200"
 *       class="rounded-full"
 *   />
 *
 * النتيجة (HTML):
 *   <img
 *     src="placeholder-200x200.svg"  (1KB SVG)
 *     data-src="real-url.webp"
 *     loading="lazy"
 *     decoding="async"
 *     width="200" height="200"
 *     class="rounded-full"
 *     onload="this.removeAttribute('data-src')"
 *   />
 *
 * فوائد:
 *   - loading="lazy" (browser native — لا JS مطلوب)
 *   - decoding="async" (لا يحجب الـ render)
 *   - placeholder SVG صغير جداً يمنع layout shift (CLS = 0)
 *   - width/height مطلوبان لـ Core Web Vitals
 */
class LazyImage extends Component
{
    public function __construct(
        public string $src,
        public string $alt = '',
        public ?int $width = null,
        public ?int $height = null,
        public string $class = '',
        public bool $eager = false, // اجعلها true للصور أعلى الـ fold (LCP)
    ) {
    }

    /**
     * placeholder SVG inline — لا request شبكة، حجم ~150 bytes.
     */
    public function placeholder(): string
    {
        $w = $this->width ?? 100;
        $h = $this->height ?? 100;
        $svg = <<<SVG
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {$w} {$h}"><rect width="100%" height="100%" fill="#f1f5f9"/></svg>
            SVG;

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    public function render(): View
    {
        return view('components.lazy-image');
    }
}
