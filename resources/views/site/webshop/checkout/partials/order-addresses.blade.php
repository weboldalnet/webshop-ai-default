{{--
    Rendelés adatlap / elérhetőség, számlázás és átvétel.

    Az adatokat a rendelésről olvassuk, nem a vevő aktuális profiljáról:
    a rendeléshez az tartozik, amit leadáskor megadott.
--}}
@php
    $wsBilling = $order->billing_data ?? [];
    $wsShipping = $order->shipping_data ?? [];

    $wsCim = function (array $adat) {
        $sor = trim(implode(' ', array_filter([
            $adat['zip'] ?? null,
            $adat['city'] ?? null,
            $adat['address'] ?? null,
        ])));

        return $sor !== '' ? $sor : null;
    };

    $wsBillingCim = $wsCim($wsBilling);
    $wsShippingCim = $wsCim($wsShipping);

    // Átvevőpontos szállításnál a csomag nem a vevő címére megy
    $wsAtvevopont = $wsShipping['parcel_shop_name'] ?? null;
    $wsAtvevopontCim = trim(implode(' ', array_filter([
        $wsShipping['parcel_shop_zip'] ?? null,
        $wsShipping['parcel_shop_city'] ?? null,
        $wsShipping['parcel_shop_address'] ?? null,
    ])));
@endphp

<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-primary text-white font-weight-bold h5">Elérhetőség és számlázás</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4 py-1">Név</dt>
                    <dd class="col-sm-8 py-1">{{ $order->customer_name ?: '—' }}</dd>

                    <dt class="col-sm-4 py-1">E-mail</dt>
                    <dd class="col-sm-8 py-1">{{ $order->customer_email ?: '—' }}</dd>

                    @if($order->customer_phone)
                        <dt class="col-sm-4 py-1">Telefon</dt>
                        <dd class="col-sm-8 py-1">{{ $order->customer_phone }}</dd>
                    @endif

                    @if($order->customer_company)
                        <dt class="col-sm-4 py-1">Cégnév</dt>
                        <dd class="col-sm-8 py-1">{{ $order->customer_company }}</dd>
                    @endif

                    @if($order->customer_tax_number)
                        <dt class="col-sm-4 py-1">Adószám</dt>
                        <dd class="col-sm-8 py-1">{{ $order->customer_tax_number }}</dd>
                    @endif

                    @if($wsBillingCim)
                        <dt class="col-sm-4 py-1">Számlázási cím</dt>
                        <dd class="col-sm-8 py-1 mb-0">
                            @if(!empty($wsBilling['name']) && $wsBilling['name'] !== $order->customer_name)
                                {{ $wsBilling['name'] }}<br>
                            @endif
                            {{ $wsBillingCim }}
                        </dd>
                    @endif
                </dl>
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-primary text-white font-weight-bold h5">Átvétel</div>
            <div class="card-body">
                @if($wsAtvevopont || $wsAtvevopontCim)
                    <p class="mb-1 font-weight-bold">{{ $wsAtvevopont ?: 'Átvevőpont' }}</p>
                    @if($wsAtvevopontCim)
                        <p class="mb-0 text-muted">{{ $wsAtvevopontCim }}</p>
                    @endif
                @elseif($wsShippingCim)
                    <p class="mb-1 font-weight-bold">Szállítási cím</p>
                    <p class="mb-0 text-muted">
                        @if(!empty($wsShipping['name']))
                            {{ $wsShipping['name'] }}<br>
                        @endif
                        {{ $wsShippingCim }}
                    </p>
                @else
                    <p class="mb-0 text-muted">
                        Személyes átvétel – a részleteket a visszaigazoló e-mailben küldjük.
                    </p>
                @endif
            </div>
        </div>
    </div>
</div>
