{{--
    A webshop külső JS-fájljainak (webshop-site.js) szövegei, pénzformátuma és végpont-alapcíme az oldal nyelvén.

    A webshop-oldalakon a webshop-site.js minden betöltési pontja előtt ott van; a többi oldalra (ahol a core layout tölti be
    a JS-t) a fejléc gombsora (global-btn-items) hozza. Többször is szerepelhet egy oldalon (ugyanazt írja). Ha egy oldalon
    egyik sincs, a JS a beépített magyar szövegekre és a /webshop alapcímre esik vissza.
--}}
@php
    $wsJsLines = __('webshop::js');
    $wsJsLines = array_merge(is_array($wsJsLines) ? $wsJsLines : [], [
        'money' => __('webshop::common.money'),
        'thousands_separator' => __('webshop::common.thousands_separator'),
        // a végpontok alapcíme a kérés nyelvén (/webshop vagy /en/webshop) — WebshopSite.url()
        'base_url' => rtrim(WsUrl::route('site.webshop.categories.index', [], false), '/'),
    ]);
@endphp
<script>window.wsI18n = @json($wsJsLines);</script>
