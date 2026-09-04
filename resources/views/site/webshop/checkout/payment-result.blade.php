@extends('site.layouts.layout')
@section('title', 'Fizetési eredmény')

@section('content')
    @include('site.webshop.partials.sticky-categories')

    <div class="ws-page-container ws-payment-result">
        <div class="container-xl container-fluid pb-5 mt-5">
            <div class="row justify-content-center">
                <div class="col-lg-8">

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif
                    @if(session('warning'))
                        <div class="alert alert-warning">{{ session('warning') }}</div>
                    @endif

                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-body text-center py-5"
                             id="ws-payment-state"
                             data-status-url="{{ route('site.webshop.payment.status', $order) }}"
                             data-pending="{{ $order->isPaymentPending() ? '1' : '0' }}">

                            <div id="ws-payment-state-body">
                                @if($order->isPaid())
                                    <div class="text-success mb-3"><i class="fa fa-check-circle fs-28"></i></div>
                                    <h2 class="font-weight-bold text-success">Sikeres fizetés!</h2>
                                    <p class="text-muted">A <strong>#{{ $order->order_number }}</strong> rendelésed fizetése sikeresen megtörtént.</p>
                                    @if($order->paid_at)
                                        <p class="text-muted small">Fizetés dátuma: {{ $order->paid_at->format('Y.m.d H:i') }}</p>
                                    @endif

                                @elseif($order->isPaymentFailed())
                                    <div class="text-danger mb-3"><i class="fa fa-times-circle fs-28"></i></div>
                                    <h2 class="font-weight-bold text-danger">Sikertelen fizetés</h2>
                                    <p class="text-muted">A <strong>#{{ $order->order_number }}</strong> rendelésed fizetése sajnos nem sikerült.</p>
                                    <p class="text-muted">Kérjük, próbálja meg újra, vagy válasszon másik fizetési módot.</p>

                                @elseif($order->isPaymentCancelled())
                                    <div class="text-warning mb-3"><i class="fa fa-ban fs-28"></i></div>
                                    <h2 class="font-weight-bold text-warning">Fizetés megszakítva</h2>
                                    <p class="text-muted">A <strong>#{{ $order->order_number }}</strong> rendelésed fizetése megszakadt.</p>
                                    <p class="text-muted">A rendelés megmarad, fizetését bármikor megismételheti.</p>

                                @elseif($order->isPaymentPending())
                                    <div class="text-info mb-3"><i class="fa fa-clock fs-28"></i></div>
                                    <h2 class="font-weight-bold text-info">Fizetés folyamatban</h2>
                                    <p class="text-muted">A <strong>#{{ $order->order_number }}</strong> rendelésed fizetése feldolgozás alatt áll.</p>
                                    <p class="text-muted ws-payment-poll-note">
                                        <i class="fa fa-spinner fa-spin mr-1"></i> Ellenőrizzük a fizetés állapotát…
                                    </p>

                                @else
                                    <div class="text-secondary mb-3"><i class="fa fa-info-circle fs-28"></i></div>
                                    <h2 class="font-weight-bold">Rendelés állapota</h2>
                                    <p class="text-muted">Rendelés száma: <strong>#{{ $order->order_number }}</strong></p>
                                    <p class="text-muted">Fizetési státusz: <strong>{{ $order->payment_status_label }}</strong></p>
                                @endif
                            </div>

                            {{-- A gombok közti térköz: ez Bootstrap 4, itt NINCS gap-* utility
                                 (az Bootstrap 5-ös), ezért a térközt a gombokra tett margó adja. --}}
                            <div class="mt-4 d-flex flex-wrap justify-content-center ws-payment-actions">
                                <a href="{{ route('site.webshop.checkout.success', $order) }}" class="btn btn-outline-secondary mx-2 mb-2">
                                    <i class="fa fa-file-invoice mr-1"></i> Rendelés részletei
                                </a>

                                {{-- Újrapróbálás csak akkor, ha a fizetés tényleg nem ment végbe.
                                     Folyamatban lévő fizetésnél félrevezető lenne: a vásárló egy
                                     már futó tranzakció mellé indítana egy másodikat. --}}
                                @if($order->isPaymentFailed() || $order->isPaymentCancelled())
                                    <form method="POST" action="{{ route('site.webshop.payment.retry', $order) }}" class="mx-2 mb-2 js-ws-payment-retry">
                                        @csrf
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fa fa-redo mr-1"></i> Fizetés újrapróbálása
                                        </button>
                                    </form>
                                @endif

                                <a href="{{ route('site.webshop.categories.index') }}" class="btn btn-outline-primary mx-2 mb-2">
                                    <i class="fa fa-shopping-bag mr-1"></i> Vissza a webshophoz
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm border-0">
                        <div class="card-header font-weight-bold">Rendelés összefoglalója</div>
                        <div class="card-body">
                            <dl class="row mb-0">
                                <dt class="col-sm-5">Rendelésszám:</dt>
                                <dd class="col-sm-7">#{{ $order->order_number }}</dd>
                                <dt class="col-sm-5">Rendelés státusz:</dt>
                                <dd class="col-sm-7" id="ws-order-status-label">{{ $order->status_label }}</dd>
                                <dt class="col-sm-5">Fizetési státusz:</dt>
                                <dd class="col-sm-7" id="ws-payment-status-label">{{ $order->payment_status_label }}</dd>
                                @if($order->payment_method)
                                    <dt class="col-sm-5">Fizetési mód:</dt>
                                    <dd class="col-sm-7">{{ \Weboldalnet\WebshopAiDefault\Services\Webshop\Commerce\WebshopCommerceService::getPaymentMethodLabel($order->payment_method) }}</dd>
                                @endif
                                @if($order->shipping_method)
                                    <dt class="col-sm-5">Szállítási mód:</dt>
                                    <dd class="col-sm-7">{{ \Weboldalnet\WebshopAiDefault\Services\Webshop\Commerce\WebshopCommerceService::getShippingMethodLabel($order->shipping_method) }}</dd>
                                @endif
                                @if(($ws['site_product_prices_visible'] ?? 'true') === 'true')
                                    <dt class="col-sm-5">Összeg:</dt>
                                    <dd class="col-sm-7 font-weight-bold text-primary">{{ hufFormat($order->total_price) }}</dd>
                                @endif
                            </dl>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            /*
                Függőben lévő fizetés követése.

                A fizetés végállapotát a provider IPN-je (szerver-szerver hívás) rögzíti,
                ami akár a vásárló visszatérése UTÁN érkezik meg – ilyenkor a vásárló egy
                már teljesült fizetésnél is a "Fizetés folyamatban" szövegen ragadna.
                Ezért fél percig kérdezgetjük az állapotot, és sikeres fizetésnél
                oldalfrissítés nélkül cseréljük a szöveget.

                A szerver oldali végpont nem csak beolvassa a tárolt állapotot: ha a
                provider tudja (queryStatus), meg is kérdezi tőle a tranzakció valódi
                állapotát, és ugyanazon az úton dolgozza fel, mint az IPN-t.
            */
            (function () {
                var panel = document.getElementById('ws-payment-state');
                if (!panel || panel.getAttribute('data-pending') !== '1') {
                    return;
                }

                var url = panel.getAttribute('data-status-url');
                var body = document.getElementById('ws-payment-state-body');
                var FIGYELES_MP = 30;
                var KERDEZES_MP = 3;
                var hatarido = Date.now() + FIGYELES_MP * 1000;
                var idozito = null;

                function leall() {
                    if (idozito) {
                        clearInterval(idozito);
                        idozito = null;
                    }
                }

                function sikeresre(adat) {
                    var datum = adat.paid_at
                        ? '<p class="text-muted small">Fizetés dátuma: ' + adat.paid_at + '</p>'
                        : '';

                    body.innerHTML =
                        '<div class="text-success mb-3"><i class="fa fa-check-circle fs-28"></i></div>' +
                        '<h2 class="font-weight-bold text-success">Sikeres fizetés!</h2>' +
                        '<p class="text-muted">A fizetés sikeresen megtörtént.</p>' + datum;

                    var fizStatusz = document.getElementById('ws-payment-status-label');
                    if (fizStatusz && adat.status_label) {
                        fizStatusz.textContent = adat.status_label;
                    }

                    var rendStatusz = document.getElementById('ws-order-status-label');
                    if (rendStatusz && adat.order_status_label) {
                        rendStatusz.textContent = adat.order_status_label;
                    }
                }

                function idotullepes() {
                    var jelzes = panel.querySelector('.ws-payment-poll-note');
                    if (jelzes) {
                        jelzes.innerHTML = 'A fizetés visszaigazolása még folyamatban van. ' +
                            'Amint megérkezik, e-mailben értesítünk – az oldalt is frissítheted.';
                    }
                }

                function ellenoriz() {
                    if (Date.now() > hatarido) {
                        leall();
                        idotullepes();
                        return;
                    }

                    fetch(url, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                        credentials: 'same-origin'
                    })
                        .then(function (valasz) { return valasz.json(); })
                        .then(function (adat) {
                            if (adat.paid) {
                                leall();
                                sikeresre(adat);
                            } else if (adat.failed || adat.cancelled) {
                                // Végállapot, de nem sikeres: itt már az újrapróbálás
                                // gomb is kell, ezért a szerver rendereli újra a lapot.
                                leall();
                                window.location.reload();
                            }
                        })
                        .catch(function () {
                            // Átmeneti hálózati hiba: a következő körben újra próbáljuk.
                        });
                }

                idozito = setInterval(ellenoriz, KERDEZES_MP * 1000);
                ellenoriz();
            })();
        </script>
    @endpush
@endsection
