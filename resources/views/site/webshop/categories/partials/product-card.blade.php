<div class="card ws-product-card h-100 shadow-sm position-relative">
    @if(($ws['site_product_prices_visible'] ?? 'true') === 'true' && $product->sale_price && $product->discount_percentage > 0)
        <div class="ws-product-sale-badge badge badge-danger position-absolute">
            -{{ $product->discount_percentage }}%
        </div>
    @endif
    @if(\Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopSettingsService::getBool('admin_product_labels_enabled') && $product->label)
        <div class="ws-product-label-badge badge position-absolute"
             style="background-color: {{ $product->label->bg_color }}; color: {{ $product->label->text_color }};
                    top: {{ (($ws['site_product_prices_visible'] ?? 'true') === 'true' && $product->sale_price && $product->discount_percentage > 0) ? '45px' : '10px' }};">
            {{ $product->label->name }}
        </div>
    @endif
    <a href="{{ route('site.webshop.products.show', $product) }}" class="ws-product-card-img text-center bg-light d-flex align-items-center justify-content-center overflow-hidden" style="height: 220px;">
        @if($product->primary_image)
            <img src="{{ $product->primary_image }}" alt="{{ $product->name }}" class="img-fluid" style="max-height: 100%;">
        @else
            <i class="fa fa-image fa-4x text-dark opacity-25 fs-30"></i>
        @endif
    </a>
    <div class="card-body d-flex py-3 flex-column">
        @if(($ws['product_secondary_name_enabled'] ?? 'false') === 'true' && $product->secondary_name)
            <h5 class="ws-product-card-title sec-title font-weight-bold mb-0">
                <a href="{{ route('site.webshop.products.show', $product) }}"
                   class="text-dark text-decoration-none">
                    {{ $product->name }}
                </a>
            </h5>
            <div class="ws-product-card-sec-title fs-14 text-muted">{{ $product->secondary_name }}</div>
        @else
            <h5 class="ws-product-card-title font-weight-bold">
                <a href="{{ route('site.webshop.products.show', $product) }}"
                   class="text-dark text-decoration-none">
                    {{ $product->name }}
                </a>
            </h5>
        @endif

        <div class="ws-product-card-badges mb-2">
            @if(($ws['product_short_desc_instead_of_properties_enabled'] ?? 'false') === 'true' && $product->short_desc)
                <div class="fs-14 text-muted mb-1 lh-12">
                    {{ \Illuminate\Support\Str::limit($product->short_desc, 100) }}
                </div>
            @else
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
            @endif
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
