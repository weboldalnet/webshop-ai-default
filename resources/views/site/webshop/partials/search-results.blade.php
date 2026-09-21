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
        <a href="{{ route('site.webshop.products.show', $wsProduct) }}" class="ws-search-item">
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
                            @if($wsProduct->sale_price)
                                <s class="text-muted mr-1">{{ hufFormat($wsProduct->price) }}</s>{{ hufFormat($wsProduct->sale_price) }}
                            @else
                                {{ hufFormat($wsProduct->price) }}
                            @endif
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
            További {{ $resultCount - $products->count() }} találat — pontosítsd a keresést.
        </div>
    @endif
@else
    <div class="ws-search-empty">
        Nincs találat a keresésre.
    </div>
@endif
