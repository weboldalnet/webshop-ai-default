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
                        <div class="card-body text-center py-5">

                            @if($order->isPaid())
                                <div class="text-success mb-3"><i class="fa fa-check-circle fa-4x"></i></div>
                                <h2 class="font-weight-bold text-success">Sikeres fizetés!</h2>
                                <p class="text-muted">A <strong>#{{ $order->order_number }}</strong> rendelésed fizetése sikeresen megtörtént.</p>
                                @if($order->paid_at)
                                    <p class="text-muted small">Fizetés dátuma: {{ $order->paid_at->format('Y.m.d H:i') }}</p>
                                @endif

                            @elseif($order->isPaymentFailed())
                                <div class="text-danger mb-3"><i class="fa fa-times-circle fa-4x"></i></div>
                                <h2 class="font-weight-bold text-danger">Sikertelen fizetés</h2>
                                <p class="text-muted">A <strong>#{{ $order->order_number }}</strong> rendelésed fizetése sajnos nem sikerült.</p>
                                <p class="text-muted">Kérjük, próbálja meg újra, vagy válasszon másik fizetési módot.</p>

                            @elseif($order->isPaymentCancelled())
                                <div class="text-warning mb-3"><i class="fa fa-ban fa-4x"></i></div>
                                <h2 class="font-weight-bold text-warning">Fizetés megszakítva</h2>
                                <p class="text-muted">A <strong>#{{ $order->order_number }}</strong> rendelésed fizetése megszakadt.</p>
                                <p class="text-muted">A rendelés megmarad, fizetését bármikor megismételheti.</p>

                            @elseif($order->isPaymentPending())
                                <div class="text-info mb-3"><i class="fa fa-clock fa-4x"></i></div>
                                <h2 class="font-weight-bold text-info">Fizetés folyamatban</h2>
                                <p class="text-muted">A <strong>#{{ $order->order_number }}</strong> rendelésed fizetése feldolgozás alatt áll.</p>
                                <p class="text-muted">Amint a fizetés megerősítést kap, értesítést küldünk.</p>

                            @else
                                <div class="text-secondary mb-3"><i class="fa fa-info-circle fa-4x"></i></div>
                                <h2 class="font-weight-bold">Rendelés állapota</h2>
                                <p class="text-muted">Rendelés száma: <strong>#{{ $order->order_number }}</strong></p>
                                <p class="text-muted">Fizetési státusz: <strong>{{ $order->payment_status_label }}</strong></p>
                            @endif

                            <div class="mt-4 d-flex flex-wrap justify-content-center gap-3">
                                <a href="{{ route('site.webshop.checkout.success', $order) }}" class="btn btn-outline-secondary">
                                    <i class="fa fa-file-invoice mr-1"></i> Rendelés részletei
                                </a>

                                @if($order->isPaymentRetryable() && !$order->isPaid())
                                    <form method="POST" action="{{ route('site.webshop.payment.retry', $order) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fa fa-redo mr-1"></i> Fizetés újrapróbálása
                                        </button>
                                    </form>
                                @endif

                                <a href="{{ route('site.webshop.categories.index') }}" class="btn btn-outline-primary">
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
                                <dd class="col-sm-7">{{ $order->status_label }}</dd>
                                <dt class="col-sm-5">Fizetési státusz:</dt>
                                <dd class="col-sm-7">{{ $order->payment_status_label }}</dd>
                                @if($order->payment_method)
                                    <dt class="col-sm-5">Fizetési mód:</dt>
                                    <dd class="col-sm-7">{{ $order->payment_method }}</dd>
                                @endif
                                @if($order->shipping_method)
                                    <dt class="col-sm-5">Szállítási mód:</dt>
                                    <dd class="col-sm-7">{{ $order->shipping_method }}</dd>
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
@endsection
