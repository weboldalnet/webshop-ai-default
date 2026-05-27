@php
    $variations = $product->variations()->active()->ordered()->get();
@endphp

@if($variations->isNotEmpty())
    <div class="card shadow-sm mt-3 ws-product-variations">
        <div class="card-body">
            <h5 class="font-weight-bold mb-3 border-bottom pb-2">Variációk</h5>
            <div class="ws-variation-list">
                @foreach($variations as $var)
                    <a href="{{ route('site.webshop.products.show', $var) }}" class="ws-product-variation-card d-flex align-items-center p-2 mb-2 border rounded text-decoration-none text-dark {{ $product->id === $var->id ? 'active border-primary bg-light' : '' }}">
                        <div class="ws-product-variation-img mr-2" style="width: 60px; height: 60px;">
                            @if($var->primary_image_thumb || $var->primary_image)
                                <img src="{{ $var->primary_image_thumb ?? $var->primary_image }}" alt="{{ $var->name }}" class="img-fluid rounded" style="width: 60px; height: 60px; object-fit: cover;">
                            @else
                                <div class="bg-light d-flex align-items-center justify-content-center rounded" style="width: 50px; height: 50px;">
                                    <i class="fa fa-image text-muted opacity-50"></i>
                                </div>
                            @endif
                        </div>
                        <div class="ws-product-variation-info lh-12">
                            <div class="ws-product-variation-title fw-600 fs-16">{{ $var->name }}</div>
                            @if(($ws['site_product_prices_visible'] ?? 'true') === 'true')
                                <div class="ws-product-variation-price text-primary fw-600 small">
                                    @include('site.webshop.partials.product-price', ['product' => $var, 'priceSize' => 'fs-16'])
                                </div>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
@endif
