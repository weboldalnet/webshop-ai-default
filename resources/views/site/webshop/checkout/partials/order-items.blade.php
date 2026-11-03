{{--
    Rendelés adatlap / tételek és a fizetendő bontása.

    A bontás ugyanazokból az eltárolt értékekből dolgozik, mint amiket a
    pénztárban látott a vásárló: a tételek összege a sorokból, a szállítási díj
    a rendelésre mentett shipping_cost, a kuponkedvezmény a discount_amount.
    Az esetleges egyéb felárat (pl. utánvét) nem találgatjuk – ami a végösszegből
    az ismert tételek után marad, azt külön soron mutatjuk.

    A kuponkedvezményt KI KELL írni: enélkül a bontás nem adja ki a végösszeget,
    és a levont összeg negatív "egyéb díj"-ként némán eltűnne.
--}}
@php
    // Egyetlen forrás: ugyanaz a bontás, amit az admin rendeléslap, a
    // visszaigazoló e-mail és a számla is használ.
    $wsBreakdown = $order->priceBreakdown();
@endphp

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-dark text-white font-weight-bold h5">{{ __('webshop::checkout.order.items_title') }}</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                <tr>
                    <th></th>
                    <th>{{ __('webshop::common.product') }}</th>
                    @if($showPrices && $order->total_price > 0)
                        <th class="text-right">{{ __('webshop::common.unit_price_label') }}</th>
                    @endif
                    <th class="text-center">{{ __('webshop::common.quantity') }}</th>
                    @if($showPrices && $order->total_price > 0)
                        <th class="text-right">{{ __('webshop::checkout.order.line_total') }}</th>
                    @endif
                </tr>
                </thead>
                <tbody>
                @foreach($order->items as $item)
                    <tr>
                        <td class="py-1" style="width: 100px;">
                            @if($item->product && $item->product->primary_image_thumb)
                                <img src="{{ $item->product->primary_image_thumb }}" alt="{{ $item->product_name }}" style="width: 80px;">
                            @endif
                        </td>
                        <td class="align-middle">
                            <span class="fw-600">{{ $item->product_name }}</span>
                            @if($item->sec_name)
                                <br><span class="font-weight-light fs-14">{{ $item->sec_name }}</span>
                            @endif
                        </td>
                        @if($showPrices && $order->total_price > 0)
                            <td class="text-right align-middle ws-nowrap">{{ WsFormat::money($item->unit_price) }}</td>
                        @endif
                        <td class="text-center align-middle ws-nowrap">{{ trans_choice('webshop::common.quantity_pcs', $item->quantity, ['count' => $item->quantity]) }}</td>
                        @if($showPrices && $order->total_price > 0)
                            <td class="text-right align-middle ws-nowrap fw-600">{{ WsFormat::money($item->total_price) }}</td>
                        @endif
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        @if($showPrices && $order->total_price > 0)
            <div class="row mt-3">
                <div class="col-lg-6 ml-auto">
                    <div class="d-flex justify-content-between py-2 border-top">
                        <span>{{ __('webshop::checkout.order.items_total') }}</span>
                        <span class="font-weight-bold">{{ WsFormat::money($wsBreakdown['items']) }}</span>
                    </div>

                    @if($order->shipping_method)
                        <div class="d-flex justify-content-between py-2 border-top">
                            <span>{{ __('webshop::checkout.order.shipping_fee') }}</span>
                            <span class="font-weight-bold">{{ $wsBreakdown['shipping'] > 0 ? WsFormat::money($wsBreakdown['shipping']) : __('webshop::checkout.order.free') }}</span>
                        </div>
                    @endif

                    @if($wsBreakdown['discount'] > 0)
                        <div class="d-flex justify-content-between py-2 border-top">
                            <span>
                                {{ __('webshop::checkout.order.coupon_discount') }}
                                @if($order->coupon_code)
                                    <span class="text-muted fs-14">({{ $order->coupon_code }})</span>
                                @endif
                            </span>
                            <span class="font-weight-bold text-success">-{{ WsFormat::money($wsBreakdown['discount']) }}</span>
                        </div>
                    @endif

                    @if($wsBreakdown['cod'] > 0)
                        <div class="d-flex justify-content-between py-2 border-top">
                            <span>{{ __('webshop::checkout.order.cod_fee') }}</span>
                            <span class="font-weight-bold">{{ WsFormat::money($wsBreakdown['cod']) }}</span>
                        </div>
                    @endif

                    @if($wsBreakdown['other'] > 0)
                        <div class="d-flex justify-content-between py-2 border-top">
                            <span>{{ __('webshop::checkout.order.other_fees') }}</span>
                            <span class="font-weight-bold">{{ WsFormat::money($wsBreakdown['other']) }}</span>
                        </div>
                    @endif

                    <div class="d-flex justify-content-between py-3 border-top h4 mb-0">
                        <span class="font-weight-bold">{{ __('webshop::common.total') }}</span>
                        <span class="text-primary font-weight-bold">{{ WsFormat::money($order->total_price) }}</span>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
