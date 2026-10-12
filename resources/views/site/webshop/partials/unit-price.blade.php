{{--
    Egységár (Ft/kg, Ft/l …) – csak ha a terméken meg van adva a kiszerelés.
    Paraméterek: $product, opcionális $unitPriceClass (alapértelmezés: külön sor, kis szürke szöveg).
--}}
@php
    $wsUnitPrice = \Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopUnitPriceService::forProduct($product);
@endphp
@if($wsUnitPrice)
    <span class="ws-unit-price {{ $unitPriceClass ?? 'd-block fs-14 font-weight-normal' }}" @unless(isset($unitPriceClass)) style="color: #5a6268;" @endunless>({{ $wsUnitPrice['formatted'] }})</span>
@endif
