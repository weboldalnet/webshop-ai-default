@if(($ws['site_product_prices_visible'] ?? 'true') === 'true')
    <div class="ws-product-price-box">
        @if($product->sale_price)
            <span class="text-muted small" style="text-decoration: line-through;">{{ hufFormat($product->price) }}</span>
            <span class="text-danger font-weight-bold d-block h5">{{ hufFormat($product->sale_price) }}</span>
        @else
            <span class="font-weight-bold d-block h5">{{ hufFormat($product->price) }}</span>
        @endif
    </div>
@endif
