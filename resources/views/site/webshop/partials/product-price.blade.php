@if(($ws['site_product_prices_visible'] ?? 'true') === 'true')
    <div class="ws-product-price-box ws-nowrap">
        @if($product->sale_price)
            <span class="text-dark" style="text-decoration: line-through;">{{ hufFormat($product->price) }}</span>
            <span class="text-danger font-weight-bold d-block {{$priceSize ?? 'h5'}} mb-0">{{ hufFormat($product->sale_price) }}</span>
        @else
            <span class="font-weight-bold d-block {{$priceSize ?? 'h5'}} mb-0">{{ hufFormat($product->price) }}</span>
        @endif
    </div>
@endif
