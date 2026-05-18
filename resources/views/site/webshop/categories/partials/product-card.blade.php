<div class="card ws-product-card h-100 shadow-sm border-0">
    <a href="{{ route('site.webshop.products.show', $product) }}" class="ws-product-card-img text-center bg-light d-flex align-items-center justify-content-center overflow-hidden" style="height: 220px;">
        @if($product->primary_image)
            <img src="{{ $product->primary_image }}" alt="{{ $product->name }}" class="img-fluid" style="max-height: 100%;">
        @else
            <i class="fa fa-image fa-4x text-dark opacity-25 fs-30"></i>
        @endif
    </a>
    <div class="card-body d-flex py-3 flex-column">
        <h5 class="ws-product-card-title font-weight-bold">
            <a href="{{ route('site.webshop.products.show', $product) }}" class="text-dark text-decoration-none">{{ $product->name }}</a>
        </h5>

        <div class="ws-product-card-badges mb-2">
            @php
                $cardProps = $product->category->propertyCategories()
                    ->wherePivot('show_on_product_card', true)
                    ->orderByPivot('sort_order')
                    ->get();
            @endphp
            @foreach($cardProps as $pc)
                @php
                    $pProps = $product->productProperties()->where('property_category_id', $pc->id)->get();
                @endphp
                @foreach($pProps as $pp)
                    @if($pp->property)
                        <span class="badge badge-secondary mb-1" title="{{ $pc->name }}">{{ $pp->property->name }}</span>
                    @elseif($pp->number_value !== null)
                        <span class="badge badge-info mb-1" title="{{ $pc->name }}">{{ (float)$pp->number_value }}{{ $pc->suffix }}</span>
                    @endif
                @endforeach
            @endforeach
        </div>

        <div class="mt-auto pt-2 border-top d-flex flex-wrap justify-content-between align-items-end">
            <div class="mr-2">
                @include('site.webshop.partials.product-price', ['product' => $product])
            </div>

            <div class="ws-product-card-actions ws-nowrap">
                @if(($ws['site_product_compare_enabled'] ?? 'false') === 'true')
                    <button type="button" class="btn btn-sm btn-outline-info js-add-to-compare" data-id="{{ $product->id }}" title="Összehasonlítás">
                        <i class="fa fa-scale-balanced"></i>
                    </button>
                @endif
                <button type="button" class="btn btn-sm btn-primary js-add-to-cart" data-id="{{ $product->id }}">
                    <i class="fa fa-shopping-cart"></i> Kosárba
                </button>
            </div>
        </div>
    </div>
</div>
