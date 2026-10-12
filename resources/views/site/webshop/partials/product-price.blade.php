{{--
    Termék ár.

    A KÜLSŐ dobozt is EZ a részlet adja, nem a hívó: ha a hívó csomagolná be, akkor
    rejtett árnál egy üres, de margós div maradna a helyén, és felesleges hézagot
    ütne a layoutba. A hívó a saját osztályait a $priceWrapClass-ban adja át.

    Akciónál az áthúzott ár az akciót megelőző 30 nap legalacsonyabb ára (Omnibusz),
    nem az alapár. A $showPriceNote (termékoldal) ezt szövegesen is kiírja.
    Opcionális: $showUnitPrice (alapértelmezés: igen) – egységár (Ft/kg, Ft/l …).
--}}
@if(($ws['site_product_prices_visible'] ?? 'true') === 'true')
    <div class="ws-product-price-box ws-nowrap {{ $priceWrapClass ?? '' }}">
        @if($product->hasActiveSale())
            <span class="text-dark" style="text-decoration: line-through;" title="Az akciót megelőző 30 nap legalacsonyabb ára">{{ hufFormat($product->referencePrice()) }}</span>
            <span class="text-danger font-weight-bold d-block {{$priceSize ?? 'h5'}} mb-0">{{ hufFormat($product->sale_price) }}</span>
            @if($showPriceNote ?? false)
                <span class="d-block fs-14 ws-price-note" style="white-space: normal; color: #5a6268;">Áthúzott ár: az akciót megelőző 30 nap legalacsonyabb ára.</span>
            @endif
        @else
            <span class="font-weight-bold d-block {{$priceSize ?? 'h5'}} mb-0">{{ hufFormat($product->sellingPrice()) }}</span>
        @endif
        @if($showUnitPrice ?? true)
            @include('site.webshop.partials.unit-price', ['product' => $product])
        @endif
    </div>
@endif
