{{--
    Termék ár.

    A KÜLSŐ dobozt is EZ a részlet adja, nem a hívó: ha a hívó csomagolná be, akkor
    rejtett árnál egy üres, de margós div maradna a helyén, és felesleges hézagot
    ütne a layoutba. A hívó a saját osztályait a $priceWrapClass-ban adja át.
--}}
@if(($ws['site_product_prices_visible'] ?? 'true') === 'true')
    <div class="ws-product-price-box ws-nowrap {{ $priceWrapClass ?? '' }}">
        @if($product->sale_price)
            <span class="text-dark" style="text-decoration: line-through;">{{ hufFormat($product->price) }}</span>
            <span class="text-danger font-weight-bold d-block {{$priceSize ?? 'h5'}} mb-0">{{ hufFormat($product->sale_price) }}</span>
        @else
            <span class="font-weight-bold d-block {{$priceSize ?? 'h5'}} mb-0">{{ hufFormat($product->price) }}</span>
        @endif
    </div>
@endif
