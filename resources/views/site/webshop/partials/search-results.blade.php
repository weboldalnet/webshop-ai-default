{{--
    A fejléc-kereső találati listája. A controller RENDERELVE adja vissza,
    a kliens csak beteszi a lenyíló dobozba.

    Az árat csak akkor írjuk ki, ha az árak láthatók – ugyanaz a kapcsoló
    vezérli, mint a termékkártyákon.
--}}
@php
    $wsPricesVisible = ($ws['site_product_prices_visible'] ?? 'true') === 'true';
@endphp

@if($products->count())
    @foreach($products as $wsProduct)
        <a href="{{ WsUrl::product($wsProduct) }}" class="ws-search-item">
            <div class="ws-search-item-img">
                @if($wsProduct->primary_image_thumb ?? $wsProduct->primary_image)
                    <img src="{{ $wsProduct->primary_image_thumb ?? $wsProduct->primary_image }}"
                         alt="{{ $wsProduct->name }}">
                @else
                    <i class="fa fa-image text-muted"></i>
                @endif
            </div>

            <div class="ws-search-item-body">
                <span class="ws-search-item-title">{{ $wsProduct->name }}</span>

                @if($wsProduct->secondary_name)
                    <span class="ws-search-item-sub">{{ $wsProduct->secondary_name }}</span>
                @endif

                <span class="ws-search-item-meta">
                    @if($wsProduct->category)
                        <span class="badge badge-light border">{{ $wsProduct->category->name_singular }}</span>
                    @endif

                    @if($wsPricesVisible)
                        <span class="ws-search-item-price">
                            @if($wsProduct->hasActiveSale())
                                <s class="text-muted mr-1" title="{{ __('webshop::product.price.reference_title') }}">{{ WsFormat::money($wsProduct->referencePrice()) }}</s>{{ WsFormat::money($wsProduct->sale_price) }}
                            @else
                                {{ WsFormat::money($wsProduct->sellingPrice()) }}
                            @endif
                            @include('site.webshop.partials.unit-price', ['product' => $wsProduct, 'unitPriceClass' => 'ml-1 text-muted'])
                        </span>
                    @endif
                </span>
            </div>
        </a>
    @endforeach

    {{-- Nincs site-oldali „összes találat" terméklista ebben a projektben,
         ezért itt csak a darabszámot jelezzük, nem linkelünk sehová. --}}
    @if($resultCount > $products->count())
        <div class="ws-search-more">
            {{ trans_choice('webshop::catalog.search.more', $resultCount - $products->count(), ['count' => $resultCount - $products->count()]) }}
        </div>
    @endif
@else
    <div class="ws-search-empty">
        {{ __('webshop::catalog.search.empty') }}
    </div>
@endif
