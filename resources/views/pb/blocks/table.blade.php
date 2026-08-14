@php
    $p = $block['props'] ?? [];
    $headers = array_values(array_filter(array_map('trim', explode('|', (string) ($p['headers'] ?? ''))), fn ($x) => $x !== ''));
    $rows = is_array($p['rows'] ?? null) ? $p['rows'] : [];
@endphp
@if(count($headers) || count($rows))
    <div class="pb-block pb-table-wrap">
        <table class="pb-table">
            @if(count($headers))
                <thead><tr>@foreach($headers as $h)<th>{{ $h }}</th>@endforeach</tr></thead>
            @endif
            <tbody>
                @foreach($rows as $row)
                    @php
                        $row = is_array($row) ? $row : [];
                        $cells = explode('|', (string) ($row['cells'] ?? ''));
                    @endphp
                    <tr>@foreach($cells as $c)<td>{{ trim($c) }}</td>@endforeach</tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
