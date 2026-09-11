{{--
    Egy kategóriaszint kirajzolása, rekurzívan.

    Külön részlet, mert a fa mélysége nincs korlátozva: a blade @include önmagára
    hivatkozva tetszőleges szintet lekezel, míg egy háromszorosan egymásba ágyazott
    @foreach fixen három szintre korlátozna.

    Elvárt változó: $nodes — [['category' => …, 'level' => int, 'children' => [...]], …]
--}}
@foreach($nodes as $node)
    @php
        $wsCat = $node['category'];
        $wsLevel = $node['level'];
        // Az inaktív kategória a site-on sem érhető el, ezért a menübe tenni sem
        // lenne értelme – a kapcsolót letiltjuk, hogy ne lehessen félreérteni.
        $wsInactive = !$wsCat->is_active;
    @endphp

    <div class="d-flex align-items-center border-bottom py-2 @if($wsInactive) text-muted @endif"
         style="padding-left: {{ $wsLevel * 28 }}px;">

        <span class="mr-2 text-muted" style="width: 16px;">
            @if($wsLevel === 0)
                <i class="fa fa-folder"></i>
            @elseif(!empty($node['children']))
                <i class="fa fa-folder-open"></i>
            @else
                <i class="fa fa-minus"></i>
            @endif
        </span>

        <span class="mr-auto @if($wsLevel === 0) font-weight-bold @endif">
            {{ $wsCat->name_singular }}
            @if($wsInactive)
                <span class="badge badge-secondary ml-1">inaktív kategória</span>
            @endif
        </span>

        <div class="custom-control custom-switch ws-nowrap">
            <input type="checkbox" class="custom-control-input js-toggle-active"
                   id="catMenu{{ $wsCat->id }}"
                   data-id="{{ $wsCat->id }}"
                   data-url="{{ route('admin.webshop.extra-settings.category-menu.toggle-active') }}"
                   @if($wsCat->show_in_sticky_header && !$wsInactive) checked @endif
                   @if($wsInactive) disabled @endif>
            <label class="custom-control-label" for="catMenu{{ $wsCat->id }}"></label>
        </div>
    </div>

    @if(!empty($node['children']))
        @include('admin.webshop.extra-settings.partials.category-menu-nodes', [
            'nodes' => $node['children'],
        ])
    @endif
@endforeach
