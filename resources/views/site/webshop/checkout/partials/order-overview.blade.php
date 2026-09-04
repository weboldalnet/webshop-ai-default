{{--
    Rendelés adatlap / áttekintés: azonosító, dátum és a három állapot.

    A vásárló szempontjából itt az a lényeg, hogy egy pillantással lássa, hol
    tart a rendelése – ezért az állapotok színes címkék, nem sima szöveg.
--}}
@php
    /* Az állapotok kódját nem tesszük közvetlenül osztálynévbe: csak az ismert
       értékekhez tartozik szín, minden máshoz semleges szürke. */
    $wsBadge = function ($status) {
        $map = [
            'paid' => 'success', 'delivered' => 'success', 'completed' => 'success', 'issued' => 'success',
            'pending' => 'info', 'processing' => 'info', 'shipped' => 'info', 'in_transit' => 'info',
            'failed' => 'danger', 'error' => 'danger',
            'cancelled' => 'warning', 'unpaid' => 'warning',
        ];

        return $map[$status] ?? 'secondary';
    };
@endphp

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-dark text-white font-weight-bold h5">Rendelés áttekintése</div>
    <div class="card-body">
        <dl class="row mb-0">
            <dt class="col-sm-4 py-1">Rendelésszám</dt>
            <dd class="col-sm-8 py-1 font-weight-bold">{{ $order->order_number }}</dd>

            <dt class="col-sm-4 py-1">Rendelés dátuma</dt>
            <dd class="col-sm-8 py-1">{{ $order->created_at ? $order->created_at->format('Y.m.d H:i') : '—' }}</dd>

            <dt class="col-sm-4 py-1">Rendelés állapota</dt>
            <dd class="col-sm-8 py-1">
                <span class="badge badge-{{ $wsBadge($order->status) }}">{{ $order->status_label }}</span>
            </dd>

            @if($order->payment_method)
                <dt class="col-sm-4 py-1">Fizetés állapota</dt>
                <dd class="col-sm-8 py-1">
                    <span class="badge badge-{{ $wsBadge($order->payment_status) }}">{{ $order->payment_status_label }}</span>
                    @if($order->paid_at)
                        <span class="text-muted small ml-1">{{ $order->paid_at->format('Y.m.d H:i') }}</span>
                    @endif
                </dd>
            @endif

            @if($order->shipping_method)
                <dt class="col-sm-4 py-1">Szállítás állapota</dt>
                <dd class="col-sm-8 py-1">
                    <span class="badge badge-{{ $wsBadge($order->shipping_status) }}">{{ $order->shipping_status_label }}</span>
                    @if($order->shipped_at)
                        <span class="text-muted small ml-1">{{ $order->shipped_at->format('Y.m.d H:i') }}</span>
                    @endif
                </dd>
            @endif

            @if($order->payment_method)
                <dt class="col-sm-4 py-1">Fizetési mód</dt>
                <dd class="col-sm-8 py-1">
                    {{ \Weboldalnet\WebshopAiDefault\Services\Webshop\Commerce\WebshopCommerceService::getPaymentMethodLabel($order->payment_method) }}
                </dd>
            @endif

            @if($order->shipping_method)
                <dt class="col-sm-4 py-1">Szállítási mód</dt>
                <dd class="col-sm-8 py-1">
                    {{ \Weboldalnet\WebshopAiDefault\Services\Webshop\Commerce\WebshopCommerceService::getShippingMethodLabel($order->shipping_method) }}
                </dd>
            @endif

            {{-- Csomagkövetés: csak akkor, ha a futárszolgálat már adott azonosítót --}}
            @if($order->shipment && $order->shipment->tracking_number)
                <dt class="col-sm-4 py-1">Csomagazonosító</dt>
                <dd class="col-sm-8 py-1">
                    @if($order->shipment->tracking_url)
                        <a href="{{ $order->shipment->tracking_url }}" target="_blank" rel="noopener">
                            {{ $order->shipment->tracking_number }} <i class="fa fa-external-link-alt small ml-1"></i>
                        </a>
                    @else
                        {{ $order->shipment->tracking_number }}
                    @endif
                </dd>
            @endif

            {{-- Számla: a számot és a kiállítás idejét mutatjuk. Letöltési link
                 szándékosan nincs: a PDF-hez ma csak admin útvonal létezik. --}}
            @if($order->invoiceDocument && $order->invoiceDocument->invoice_number)
                <dt class="col-sm-4 py-1">Számlaszám</dt>
                <dd class="col-sm-8 py-1">
                    {{ $order->invoiceDocument->invoice_number }}
                    @if($order->invoiceDocument->issued_at)
                        <span class="text-muted small ml-1">{{ $order->invoiceDocument->issued_at->format('Y.m.d') }}</span>
                    @endif
                </dd>
            @endif
        </dl>
    </div>
</div>
