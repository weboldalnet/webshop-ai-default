@extends('site.layouts.layout')
@section('title', __('webshop::checkout.payment_result.title'))

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
                             data-status-url="{{ WsUrl::route('site.webshop.payment.status', $order) }}"
                             data-pending="{{ $order->isPaymentPending() ? '1' : '0' }}">

                            {{-- :order / :number / :status: a jelölés itt készül, az érték e()-vel védve --}}
                            @php($wsOrderRef = '<strong>#' . e($order->order_number) . '</strong>')
                            <div id="ws-payment-state-body">
                                @if($order->isPaid())
                                    <div class="text-success mb-3"><i class="fa fa-check-circle fs-28"></i></div>
                                    <h2 class="font-weight-bold text-success">{{ __('webshop::checkout.payment_result.paid_title') }}</h2>
                                    <p class="text-muted">{!! __('webshop::checkout.payment_result.paid_text', ['order' => $wsOrderRef]) !!}</p>
                                    @if($order->paid_at)
                                        <p class="text-muted small">{{ __('webshop::checkout.payment_result.paid_at', ['date' => WsFormat::datetime($order->paid_at)]) }}</p>
                                    @endif

                                @elseif($order->isPaymentFailed())
                                    <div class="text-danger mb-3"><i class="fa fa-times-circle fs-28"></i></div>
                                    <h2 class="font-weight-bold text-danger">{{ __('webshop::checkout.payment_result.failed_title') }}</h2>
                                    <p class="text-muted">{!! __('webshop::checkout.payment_result.failed_text', ['order' => $wsOrderRef]) !!}</p>
                                    <p class="text-muted">{{ __('webshop::checkout.payment_result.failed_hint') }}</p>

                                @elseif($order->isPaymentCancelled())
                                    <div class="text-warning mb-3"><i class="fa fa-ban fs-28"></i></div>
                                    <h2 class="font-weight-bold text-warning">{{ __('webshop::checkout.payment_result.cancelled_title') }}</h2>
                                    <p class="text-muted">{!! __('webshop::checkout.payment_result.cancelled_text', ['order' => $wsOrderRef]) !!}</p>
                                    <p class="text-muted">{{ __('webshop::checkout.payment_result.cancelled_hint') }}</p>

                                @elseif($order->isPaymentPending())
                                    <div class="text-info mb-3"><i class="fa fa-clock fs-28"></i></div>
                                    <h2 class="font-weight-bold text-info">{{ __('webshop::checkout.payment_result.pending_title') }}</h2>
                                    <p class="text-muted">{!! __('webshop::checkout.payment_result.pending_text', ['order' => $wsOrderRef]) !!}</p>
                                    <p class="text-muted ws-payment-poll-note">
                                        <i class="fa fa-spinner fa-spin mr-1"></i> {{ __('webshop::checkout.payment_result.pending_checking') }}
                                    </p>

                                @else
                                    <div class="text-secondary mb-3"><i class="fa fa-info-circle fs-28"></i></div>
                                    <h2 class="font-weight-bold">{{ __('webshop::checkout.payment_result.other_title') }}</h2>
                                    <p class="text-muted">{!! __('webshop::checkout.payment_result.order_number', ['number' => $wsOrderRef]) !!}</p>
                                    <p class="text-muted">{!! __('webshop::checkout.payment_result.payment_status', ['status' => '<strong>' . e($order->payment_status_label) . '</strong>']) !!}</p>
                                @endif
                            </div>

                            {{-- A gombok közti térköz: ez Bootstrap 4, itt NINCS gap-* utility
                                 (az Bootstrap 5-ös), ezért a térközt a gombokra tett margó adja. --}}
                            <div class="mt-4 d-flex flex-wrap justify-content-center ws-payment-actions">
                                <a href="{{ WsUrl::route('site.webshop.checkout.success', $order) }}" class="btn btn-outline-secondary mx-2 mb-2">
                                    <i class="fa fa-file-invoice mr-1"></i> {{ __('webshop::checkout.payment_result.order_details') }}
                                </a>

                                {{-- Újrapróbálás csak akkor, ha a fizetés tényleg nem ment végbe.
                                     Folyamatban lévő fizetésnél félrevezető lenne: a vásárló egy
                                     már futó tranzakció mellé indítana egy másodikat. --}}
                                @if($order->isPaymentFailed() || $order->isPaymentCancelled())
                                    <form method="POST" action="{{ WsUrl::route('site.webshop.payment.retry', $order) }}" class="mx-2 mb-2 js-ws-payment-retry">
                                        @csrf
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fa fa-redo mr-1"></i> {{ __('webshop::checkout.payment_result.retry') }}
                                        </button>
                                    </form>
                                @endif

                                <a href="{{ WsUrl::route('site.webshop.categories.index') }}" class="btn btn-outline-primary mx-2 mb-2">
                                    <i class="fa fa-shopping-bag mr-1"></i> {{ __('webshop::checkout.back_to_shop') }}
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm border-0">
                        <div class="card-header font-weight-bold">{{ __('webshop::checkout.payment_result.summary_title') }}</div>
                        <div class="card-body">
                            <dl class="row mb-0">
                                <dt class="col-sm-5">{{ __('webshop::checkout.payment_result.summary_order_number') }}</dt>
                                <dd class="col-sm-7">#{{ $order->order_number }}</dd>
                                <dt class="col-sm-5">{{ __('webshop::checkout.payment_result.summary_order_status') }}</dt>
                                <dd class="col-sm-7" id="ws-order-status-label">{{ $order->status_label }}</dd>
                                <dt class="col-sm-5">{{ __('webshop::checkout.payment_result.summary_payment_status') }}</dt>
                                <dd class="col-sm-7" id="ws-payment-status-label">{{ $order->payment_status_label }}</dd>
                                @if($order->payment_method)
                                    <dt class="col-sm-5">{{ __('webshop::checkout.payment_result.summary_payment_method') }}</dt>
                                    <dd class="col-sm-7">{{ \Weboldalnet\WebshopAiDefault\Services\Webshop\Commerce\WebshopCommerceService::getPaymentMethodLabel($order->payment_method) }}</dd>
                                @endif
                                @if($order->shipping_method)
                                    <dt class="col-sm-5">{{ __('webshop::checkout.payment_result.summary_shipping_method') }}</dt>
                                    <dd class="col-sm-7">{{ \Weboldalnet\WebshopAiDefault\Services\Webshop\Commerce\WebshopCommerceService::getShippingMethodLabel($order->shipping_method) }}</dd>
                                @endif
                                @if(($ws['site_product_prices_visible'] ?? 'true') === 'true')
                                    <dt class="col-sm-5">{{ __('webshop::checkout.payment_result.summary_amount') }}</dt>
                                    <dd class="col-sm-7 font-weight-bold text-primary">{{ WsFormat::money($order->total_price) }}</dd>
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
                var WATCH_SECONDS = 30;
                var POLL_INTERVAL_SECONDS = 3;
                var deadline = Date.now() + WATCH_SECONDS * 1000;
                var timer = null;
                var paidTitleText = @json(__('webshop::checkout.payment_result.paid_title'));
                var paidText = @json(__('webshop::checkout.payment_result.paid_text_short'));
                var paidAtText = @json(__('webshop::checkout.payment_result.paid_at'));
                var timeoutText = @json(__('webshop::checkout.payment_result.pending_timeout'));

                function stopPolling() {
                    if (timer) {
                        clearInterval(timer);
                        timer = null;
                    }
                }

                function showSuccess(payload) {
                    var paidAtHtml = payload.paid_at
                        ? '<p class="text-muted small">' + paidAtText.replace(':date', function () { return payload.paid_at; }) + '</p>'
                        : '';

                    body.innerHTML =
                        '<div class="text-success mb-3"><i class="fa fa-check-circle fs-28"></i></div>' +
                        '<h2 class="font-weight-bold text-success">' + paidTitleText + '</h2>' +
                        '<p class="text-muted">' + paidText + '</p>' + paidAtHtml;

                    var paymentStatusLabel = document.getElementById('ws-payment-status-label');
                    if (paymentStatusLabel && payload.status_label) {
                        paymentStatusLabel.textContent = payload.status_label;
                    }

                    var orderStatusLabel = document.getElementById('ws-order-status-label');
                    if (orderStatusLabel && payload.order_status_label) {
                        orderStatusLabel.textContent = payload.order_status_label;
                    }
                }

                function onTimeout() {
                    var notice = panel.querySelector('.ws-payment-poll-note');
                    if (notice) {
                        notice.innerHTML = timeoutText;
                    }
                }

                function checkStatus() {
                    if (Date.now() > deadline) {
                        stopPolling();
                        onTimeout();
                        return;
                    }

                    fetch(url, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                        credentials: 'same-origin'
                    })
                        .then(function (response) { return response.json(); })
                        .then(function (payload) {
                            if (payload.paid) {
                                stopPolling();
                                showSuccess(payload);
                            } else if (payload.failed || payload.cancelled) {
                                // Végállapot, de nem sikeres: itt már az újrapróbálás
                                // gomb is kell, ezért a szerver rendereli újra a lapot.
                                stopPolling();
                                window.location.reload();
                            }
                        })
                        .catch(function () {
                            // Átmeneti hálózati hiba: a következő körben újra próbáljuk.
                        });
                }

                timer = setInterval(checkStatus, POLL_INTERVAL_SECONDS * 1000);
                checkStatus();
            })();
        </script>
    @endpush
@endsection
