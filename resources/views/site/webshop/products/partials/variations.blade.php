@php
    /*
       A TELJES variációcsoportot mutatjuk, a jelenlegi terméket is beleértve –
       a variations() reláció csak a TÖBBIT adja vissza. Enélkül a látogató nem
       látta, melyik változaton áll éppen, és a lista eggyel kevesebb elemű volt,
       mint ahány változat valójában van.
    */
    $variations = $product->variations()->active()->get()
        ->push($product)
        ->sortBy([['sort_order', 'asc'], ['id', 'asc']])
        ->values();
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
                            {{-- A látható/rejtett árat maga a részlet dönti el, itt nem kell újra --}}
                            @include('site.webshop.partials.product-price', [
                                'product' => $var,
                                'priceSize' => 'fs-16',
                                'priceWrapClass' => 'ws-product-variation-price text-primary fw-600 small',
                            ])
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
@endif
