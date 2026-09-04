@extends('site.layouts.layout')
@section('title', ($ws['site_checkout_mode'] ?? 'order') == 'quote' ? 'Ajánlatkérés' : 'Rendelés leadása')

@section('content')
    @include('site.webshop.partials.sticky-categories')

    @php
        $wsPricesVisible = ($ws['site_product_prices_visible'] ?? 'true') === 'true';
        // Van-e egyáltalán választható szállítási mód (ajánlatkérésnél nincs)
        $wsShippingEnabled = !$isQuoteMode && !empty($shippingMethods);
        $wsAddressEnabled = ($ws['site_checkout_shipping_enabled'] ?? 'false') === 'true';
        // A szállítási doboznak csak akkor van tartalma, ha van mód VAGY cím
        $wsHasShippingCard = $wsShippingEnabled || $wsAddressEnabled;
    @endphp

    <div class="ws-page-container ws-checkout-index">
        <div class="container-xl container-fluid pb-5 mt-5">

            @include('site.webshop.checkout.partials.alerts')

            <div class="row">
                <div class="col-lg-12">
                    <h1 class="mb-4 font-weight-bold border-bottom pb-2">
                        <i class="fa {{ ($ws['site_checkout_mode'] ?? 'order') == 'quote' ? 'fa-file-invoice' : 'fa-cart-check' }} mr-2"></i>
                        {{ ($ws['site_checkout_mode'] ?? 'order') == 'quote' ? 'Ajánlatkérés' : 'Pénztár' }}
                    </h1>
                </div>
            </div>

            {{-- Adminban szerkeszthető értesítő a kosár doboz fölött --}}
            <div class="row">
                <div class="col-12">
                    @include('site.webshop.checkout.partials.notice', [
                        'noticeContent' => $ws['site_checkout_notice_top_content'] ?? null,
                        'noticeType' => $ws['site_checkout_notice_top_type'] ?? 'info',
                    ])
                </div>
            </div>

            {{-- 1. Kosár tartalma – teljes szélességben, a formon kívül --}}
            @include('site.webshop.checkout.partials.cart-items')

            <form method="POST" action="{{ route('site.webshop.checkout.store') }}" class="row">
                @csrf

                {{-- 2. Személyes adatok --}}
                <div class="col-lg-6 mb-4">
                    @include('site.webshop.checkout.partials.personal-data')
                </div>

                <div class="col-lg-6 mb-4">
                    {{-- 3. Szállítási adatok --}}
                    @if($wsHasShippingCard)
                        @include('site.webshop.checkout.partials.shipping')
                    @endif

                    {{-- 4. Fizetési lehetőségek --}}
                    @if(!$isQuoteMode)
                        @include('site.webshop.checkout.partials.payment')
                    @endif
                </div>

                {{-- Kérdőív a Megjegyzés fölött, ha van beállítva --}}
                @if($checkoutQa ?? null)
                    <div class="col-12 mb-4">
                        @include('site.webshop.checkout.partials.qa')
                    </div>
                @endif

                {{-- 5. Megjegyzés – teljes szélességben --}}
                <div class="col-12 mb-4">
                    @include('site.webshop.checkout.partials.note')
                </div>

                {{-- Adminban szerkeszthető értesítő a rendelés leadása doboz fölött --}}
                <div class="col-12">
                    @include('site.webshop.checkout.partials.notice', [
                        'noticeContent' => $ws['site_checkout_notice_bottom_content'] ?? null,
                        'noticeType' => $ws['site_checkout_notice_bottom_type'] ?? 'info',
                    ])
                </div>

                {{-- 6. Tételes összesítő és a rendelés leadása --}}
                <div class="col-12">
                    @include('site.webshop.checkout.partials.summary')
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        @include('site.webshop.checkout.partials.scripts')
    @endpush
@endsection
