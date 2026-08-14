@php
    $p = $block['props'] ?? [];
    $items = is_array($p['items'] ?? null) ? $p['items'] : [];
    $labels = ['facebook' => 'فيسبوك', 'twitter' => 'إكس', 'instagram' => 'إنستغرام',
        'linkedin' => 'لينكدإن', 'youtube' => 'يوتيوب', 'whatsapp' => 'واتساب', 'telegram' => 'تيليغرام'];
    $icons = ['facebook' => 'f', 'twitter' => '𝕏', 'instagram' => '◎',
        'linkedin' => 'in', 'youtube' => '▶', 'whatsapp' => '✆', 'telegram' => '✈'];
@endphp
@if(count($items))
    <div class="pb-block pb-social">
        @foreach($items as $item)
            @php
                $item = is_array($item) ? $item : [];
                $net = isset($labels[$item['network'] ?? '']) ? $item['network'] : '';
            @endphp
            @if($net !== '' && ! empty($item['url']))
                <a class="pb-social-link pb-social-{{ $net }}" href="{{ safe_url($item['url']) }}"
                   target="_blank" rel="noopener noreferrer" aria-label="{{ $labels[$net] }}">{{ $icons[$net] }}</a>
            @endif
        @endforeach
    </div>
@endif
