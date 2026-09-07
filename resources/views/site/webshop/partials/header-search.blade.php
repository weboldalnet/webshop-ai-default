{{--
    Szabadszavas termékkereső a fejlécbe.

    A nemesmag projekt fejléc-keresőjének mintája: gépelés közben AJAX-szal
    kér RENDERELT HTML-t, és azt teszi a lenyíló dobozba.

    A stílus és a viselkedés a CSOMAG assetjeiben van, nem itt:
      - stílus: public/packages/webshop/site/css/webshop-site.scss
                A site layout ugyanazzal az @isset($ws) feltétellel tölti be,
                amivel ez a markup is kirenderelődik – tehát ahol a kereső
                látszik, ott a stílusa is ott van.
      - viselkedés: public/packages/webshop/site/js/webshop-search.js
                Külön fájl, mert a webshop-site.js NEM minden webshop-oldalon
                töltődik be (pl. a rendelés-visszaigazolón és a fizetési
                eredmény oldalon nem), a kereső viszont ott is megjelenik.

    Ez a részlet csak bekapcsolt keresőnél kerül ki (a feltétel a
    global-btn-items.blade.php-ban van), így a JS is csak akkor töltődik le.
--}}
<div class="ws-search-container" data-url="{{ route('site.webshop.products.search') }}">
    <input type="text"
           class="form-control ws-search-input h-100 border-0"
           placeholder="Keresés (min. 3 karakter)"
           autocomplete="off"
           aria-label="Termékkeresés">

    <div class="ws-search-results d-none"></div>
</div>

<script src="/packages/webshop/site/js/webshop-search.js" defer></script>
