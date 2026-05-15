@if($products->isEmpty())
    <div class="alert alert-info">Nincs a megadott feltételeknek megfelelő termék a kategóriában.</div>
@else
    @if($viewMode === 'table')
        @include('site.webshop.categories.partials.product-table', ['products' => $products, 'ws' => $ws])
    @else
        <div class="row ws-product-list">
            @php
                $colClass = ($ws['site_category_cards_per_row'] ?? '3') == '4' ? 'col-lg-3' : 'col-lg-4';
            @endphp
            @foreach($products as $product)
                <div class="{{ $colClass }} col-md-6 mb-4">
                    @include('site.webshop.categories.partials.product-card', ['product' => $product, 'ws' => $ws])
                </div>
            @endforeach
        </div>
    @endif
@endif
