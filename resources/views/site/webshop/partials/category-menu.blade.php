{{--
    Egyedi, több szintű kategória menüsor.

    A nemesmag fejléc-menüjének mintája: vízszintes sáv a legfelső kategóriákkal,
    föléjük húzva teljes szélességű panel nyílik le a mélyebb szintekkel.

    Csak akkor kerül ki, ha a többszintes kategóriarendszer ÉS az egyedi menüsor is
    be van kapcsolva – a feltétel a sticky-categories.blade.php-ban van.

    ASZTALI és MOBIL nézet szándékosan más:
      - asztali: lenyíló mega-menü (lg felett)
      - mobil:   csak két kompakt gomb, a kategóriák bal oldalt kicsúszó fiókban
                 (egy 15x15x30-as kategóriafa vízszintes görgetősávon használhatatlan)

    A stílus a webshop-site.css-ben, a viselkedés a webshop-category-menu.js-ben van.
--}}
<?php /** @var \Weboldalnet\WebshopAiDefault\Models\WebshopCategory $wsCat */ ?>
@php
    // A composer minden menübe kapcsolt kategóriát visszaad, szinttől függetlenül –
    // a sávba csak a gyökérszintűek valók.
    $wsMenuRoots = $stickyCategories->where('parent_id', null);
@endphp

<div class="ws-cat-menu sticky-top bg-white shadow-sm">
    <div class="container-xl container-fluid position-relative">

        {{-- ASZTALI: lenyíló mega-menü --}}
        <div class="ws-cat-menu-bar d-none d-lg-flex align-items-center">
            @foreach($wsMenuRoots as $wsCat)
                @php($wsHasChildren = $wsCat->children->isNotEmpty())
                <a href="{{ route('site.webshop.categories.show', $wsCat) }}"
                   class="ws-cat-menu-btn @if(isset($category) && ($category->id === $wsCat->id || $category->parent_id === $wsCat->id)) active @endif"
                   @if($wsHasChildren) data-ws-panel="#wsCatPanel{{ $wsCat->id }}" @endif>
                    @if($wsCat->icon)
                        <img src="{{ $wsCat->icon }}" alt="" class="ws-cat-menu-icon">
                    @endif
                    <span>{{ $wsCat->name_singular }}</span>
                    @if($wsHasChildren)
                        <i class="fa fa-angle-down ml-1"></i>
                    @endif
                </a>
            @endforeach
        </div>

        {{-- ASZTALI: a lenyíló panelek.
             A sávon KÍVÜL, de a position:relative konténeren belül vannak, hogy a
             teljes szélességet ki tudják tölteni. --}}
        @foreach($wsMenuRoots as $wsCat)
            @if($wsCat->children->isNotEmpty())
                {{-- A panelen SEMMILYEN Bootstrap display utility (d-none / d-lg-block) nem
                     lehet: azok !important-tal jönnek, és felülírnák a .show kapcsolót –
                     minden panel egyszerre látszana, egymásra rajzolva. Az "csak asztali"
                     viselkedést a CSS media query adja. --}}
                <div id="wsCatPanel{{ $wsCat->id }}" class="ws-cat-panel">
                    <div class="row">
                        @foreach($wsCat->children as $wsChild)
                            <div class="col-lg-4 col-xl-3">
                                <div class="ws-cat-panel-col">
                                    <a href="{{ route('site.webshop.categories.show', $wsChild) }}"
                                       class="ws-cat-panel-child">{{ $wsChild->name_singular }}</a>

                                    @foreach($wsChild->children as $wsGrandChild)
                                        <a href="{{ route('site.webshop.categories.show', $wsGrandChild) }}"
                                           class="ws-cat-panel-sub">{{ $wsGrandChild->name_singular }}</a>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach

        {{-- MOBIL: két kompakt gomb --}}
        <div class="ws-cat-menu-mobile d-flex d-lg-none align-items-center">
            @isset($visibleFilterBtn)
                <button type="button" class="ws-cat-menu-mobile-btn js-show-filter-btn">
                    <i class="fa fa-filter mr-1"></i> Szűrés
                </button>
            @endisset

            <button type="button" class="ws-cat-menu-mobile-btn js-ws-cat-drawer-open">
                <i class="fa fa-bars mr-1"></i> Kategóriák
            </button>
        </div>
    </div>
</div>

{{-- MOBIL: a bal oldalt kicsúszó kategória fiók.
     Ugyanaz a mechanika, mint a szűrő fióknál: a külső réteg adja a sötét hátteret
     és a kívülre kattintást, a belső doboz csúszik be. --}}
<div class="ws-cat-drawer d-lg-none">
    <div class="ws-cat-drawer-box">
        <div class="ws-cat-drawer-header">
            <span class="font-weight-bold">Kategóriák</span>
            <button type="button" class="ws-cat-drawer-close js-ws-cat-drawer-close" aria-label="Bezárás">
                <i class="fa fa-times"></i>
            </button>
        </div>

        <div class="ws-cat-drawer-body">
            @foreach($wsMenuRoots as $wsCat)
                @include('site.webshop.partials.category-menu-mobile-node', [
                    'wsNode' => $wsCat,
                    'wsLevel' => 0,
                ])
            @endforeach
        </div>
    </div>
</div>

<script src="/packages/webshop/site/js/webshop-category-menu.js" defer></script>
