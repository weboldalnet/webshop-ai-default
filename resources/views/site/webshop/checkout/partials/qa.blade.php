{{--
    Pénztári kérdőív (a Webshop beállításokban kiválasztott ContactQa).

    A kérdés-megjelenítést nem építjük újra: a kapcsolat oldal évek óta működő
    nézeteit használjuk (site.elements.contact.*), így a mezőnevek is azonosak
    (answers[<kérdés id>][question] / [answers][]) – erre épül a mentés is
    a WebshopCheckoutQaService-ben.

    A view()->exists() azért kell, mert ezek a nézetek a PROJEKTHEZ tartoznak,
    nem a csomaghoz: kérdőív-nézetek nélküli projektben a blokk kimarad,
    nem visz 500-ba.
--}}
@php
    $wsQaViewExists = view()->exists('site.elements.contact.qa-items.qa-object-list');
@endphp

@if($checkoutQa && !empty($checkoutQa->elements) && $wsQaViewExists)
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white font-weight-bold h5">{{ $checkoutQa->name }}</div>
        <div class="card-body">
            <div class="row">
                @include('site.elements.contact.qa-items.qa-object-list', ['contactQaId' => $checkoutQa->id])
            </div>
        </div>
    </div>
@endif
