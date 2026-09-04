{{--
    Termékblokk.

    Itt SZÁNDÉKOSAN nincs saját "További termékek" gomb: minden blokk gombjai a
    közös gomb-szerkesztőből jönnek (buttons mező), és a block.blade.php
    rendereli őket a site.elements.items.buttons-2 partiallal. Két gombmechanizmus
    egymás mellett duplán jelenítené meg ugyanazt.
--}}
@include('site.webshop.home.partials.card-row', [
    'cardView' => 'site.webshop.categories.partials.product-card',
    'cardVar'  => 'product',
])
