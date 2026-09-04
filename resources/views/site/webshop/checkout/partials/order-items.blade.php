{{--
    Rendelés adatlap / tételek és a fizetendő bontása.

    A bontás ugyanazokból az eltárolt értékekből dolgozik, mint amiket a
    pénztárban látott a vásárló: a tételek összege a sorokból, a szállítási díj
    a rendelésre mentett shipping_cost. Az esetleges egyéb felárat (pl. utánvét)
    nem találgatjuk – ami a végösszegből a két ismert tétel után marad, azt
    külön soron mutatjuk.
--}}
@php
    $wsTetelekOsszege = $order->items->sum('total_price');
    $wsSzallitas = (float) ($order->shipping_cost ?? 0);
    $wsEgyeb = round((float) $order->total_price - $wsTetelekOsszege - $wsSzallitas, 2);
@endphp

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-dark text-white font-weight-bold h5">Rendelt termékek</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                <tr>
                    <th></th>
                    <th>Termék</th>
                    @if($showPrices && $order->total_price > 0)
                        <th class="text-right">Egységár</th>
                    @endif
                    <th class="text-center">Mennyiség</th>
                    @if($showPrices && $order->total_price > 0)
                        <th class="text-right">Sorösszeg</th>
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
                            <td class="text-right align-middle ws-nowrap">{{ hufFormat($item->unit_price) }}</td>
                        @endif
                        <td class="text-center align-middle ws-nowrap">{{ $item->quantity }} db</td>
                        @if($showPrices && $order->total_price > 0)
                            <td class="text-right align-middle ws-nowrap fw-600">{{ hufFormat($item->total_price) }}</td>
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
                        <span>Termékek összesen</span>
                        <span class="font-weight-bold">{{ hufFormat($wsTetelekOsszege) }}</span>
                    </div>

                    @if($order->shipping_method)
                        <div class="d-flex justify-content-between py-2 border-top">
                            <span>Szállítási díj</span>
                            <span class="font-weight-bold">{{ $wsSzallitas > 0 ? hufFormat($wsSzallitas) : 'Ingyenes' }}</span>
                        </div>
                    @endif

                    @if($wsEgyeb > 0)
                        <div class="d-flex justify-content-between py-2 border-top">
                            <span>Egyéb díjak</span>
                            <span class="font-weight-bold">{{ hufFormat($wsEgyeb) }}</span>
                        </div>
                    @endif

                    <div class="d-flex justify-content-between py-3 border-top h4 mb-0">
                        <span class="font-weight-bold">Összesen</span>
                        <span class="text-primary font-weight-bold">{{ hufFormat($order->total_price) }}</span>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
