{{--
    A fejléc jobb oldali gombsora.

    A kereső a gomboktól BALRA jelenik meg, ha az admin bekapcsolta.
    A feltétel itt van, nem a keresőben: így kikapcsolt állapotban a markup
    sem kerül ki, és a hozzá tartozó AJAX-végpont sem hívódhat meg.
--}}
<div class="ws-global-btn-items">
    @if(($ws['site_header_search_enabled'] ?? 'false') === 'true')
        @include('site.webshop.partials.header-search')
    @endif

    @include('site.webshop.partials.compare-button')
    @include('site.webshop.partials.cart-button')
</div>