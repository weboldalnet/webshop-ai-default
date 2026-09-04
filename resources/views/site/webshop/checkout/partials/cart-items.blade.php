{{--
    Pénztár / 1. blokk: a kosár tartalma, teljes szélességben.

    Szándékosan a <form>-on KÍVÜL áll: a mennyiség módosítása és a törlés
    AJAX-on megy (webshop-site.js), nem a rendelés elküldött adatai közé tartozik.

    Itt csak a kosár összege szerepel – a fizetendő végösszeg a lap alján,
    a tételes összesítőben áll (partials/summary.blade.php).
--}}
<div class="row">
    <div class="col-12 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-dark text-white font-weight-bold h5">A kosár tartalma</div>
            <div class="card-body">
                @foreach($items as $productId => $item)
                    <div class="mb-3 pb-3 border-bottom ws-checkout-item-box">
                        <div class="d-flex align-items-center ws-checkout-item" data-id="{{ $productId }}">
                            <div class="mr-3" style="width: 80px; height: 80px;">
                                @if($item['image'])
                                    <img src="{{ $item['image_thumb'] ?? $item['image'] }}" class="rounded border" style="width: 80px; height: 80px; object-fit: cover">
                                @else
                                    <div class="bg-light text-center p-2 rounded"><i class="fa fa-image text-muted"></i></div>
                                @endif
                            </div>

                            <div class="flex-grow-1">
                                <h5 class="fs-16 font-weight-bold mb-1">
                                    {{ $item['name'] }}
                                    @if($item['sec_name'])
                                        @if(!$wsPricesVisible) <br> @endif
                                        <span class="font-weight-light fs-14">{{ $item['sec_name'] }}</span>
                                    @endif
                                </h5>

                                @if($wsPricesVisible)
                                    <div class="text-muted small lh-12">Egységár: {{ hufFormat($item['price']) }}</div>
                                    <div class="d-md-none d-block font-weight-bold fs-16 mt-1" style="min-width: 100px;">
                                        {{ hufFormat($item['quantity'] * $item['price']) }}
                                    </div>
                                @endif

                                <div class="d-md-flex d-none align-items-center mt-2">
                                    <div class="input-group input-group-sm" style="width: 100px;">
                                        <div class="input-group-prepend">
                                            <button class="btn btn-outline-secondary js-qty-minus" type="button" data-id="{{ $productId }}">-</button>
                                        </div>
                                        <input type="text" class="form-control text-center js-qty-input" value="{{ $item['quantity'] }}" readonly data-id="{{ $productId }}">
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary js-qty-plus" type="button" data-id="{{ $productId }}">+</button>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-link text-danger ml-3 js-remove-cart-item ws-nowrap" data-id="{{ $productId }}" data-reload="true">
                                        <i class="fa fa-trash-alt mr-1"></i> Törlés
                                    </button>
                                </div>
                            </div>

                            @if($wsPricesVisible)
                                <div class="d-md-block d-none text-right font-weight-bold ml-2 fs-16" style="min-width: 100px;">
                                    {{ hufFormat($item['quantity'] * $item['price']) }}
                                </div>
                            @endif
                        </div>

                        <div class="d-md-none d-flex justify-content-between align-items-center mt-2">
                            <div class="input-group input-group-sm" style="width: 100px;">
                                <div class="input-group-prepend">
                                    <button class="btn btn-outline-secondary js-qty-minus" type="button" data-id="{{ $productId }}">-</button>
                                </div>
                                <input type="text" class="form-control text-center js-qty-input" value="{{ $item['quantity'] }}" readonly data-id="{{ $productId }}">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary js-qty-plus" type="button" data-id="{{ $productId }}">+</button>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-link text-danger ml-3 js-remove-cart-item ws-nowrap" data-id="{{ $productId }}" data-reload="true">
                                <i class="fa fa-trash-alt mr-1"></i> Törlés
                            </button>
                        </div>
                    </div>
                @endforeach

                @if($wsPricesVisible)
                    <div class="d-flex flex-sm-row flex-column justify-content-between align-items-center mt-4 pt-3 border-top h4 mb-0">
                        <span class="font-weight-bold">Kosár összeg:</span>
                        <span class="text-primary font-weight-bold js-cart-total">{{ hufFormat($total) }}</span>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
