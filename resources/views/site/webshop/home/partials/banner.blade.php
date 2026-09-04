{{--
    Banner blokk — három képváltozat art direction-nel.

    A <picture> forrásai a NAGYOBB képernyő felől szűkülnek: a böngésző az első
    illeszkedő source-t választja. Ha egy változat hiányzik, a lánc automatikusan
    a következő meglévőre esik vissza, tehát elég csak az asztali képet feltölteni.

    A töréspontok a metszők szélességéhez igazodnak (1320 / 1024 / 420).
--}}
@php
    $wsDesktop = $block->img_desktop;
    $wsTablet = $block->img_tablet;
    $wsMobile = $block->img_mobile;

    // A fallback mindig a legnagyobb meglévő kép
    $wsFallback = $wsDesktop ?: ($wsTablet ?: $wsMobile);
@endphp

@if($wsFallback)
    @php
        $wsImg = '<picture>'
            . ($wsDesktop ? '<source media="(min-width: 992px)" srcset="' . e($wsDesktop) . '">' : '')
            . ($wsTablet ? '<source media="(min-width: 576px)" srcset="' . e($wsTablet) . '">' : '')
            . ($wsMobile ? '<source media="(max-width: 575.98px)" srcset="' . e($wsMobile) . '">' : '')
            . '<img src="' . e($wsFallback) . '" alt="' . e($block->img_alt ?? '') . '" class="w-100 d-block rounded">'
            . '</picture>';
    @endphp

    <div class="ws-home-banner">
        @if($block->link_url)
            <a href="{{ $block->link_url }}" class="d-block">{!! $wsImg !!}</a>
        @else
            {!! $wsImg !!}
        @endif
    </div>
@endif
