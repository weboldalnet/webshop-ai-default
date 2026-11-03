@extends('site.layouts.layout')
@section('title', ($ws['site_checkout_mode'] ?? 'order') == 'quote' ? __('webshop::checkout.page.quote_title') : __('webshop::checkout.page.title'))

@section('content')
    @include('site.webshop.partials.sticky-categories')

    @php
        $wsPricesVisible = ($ws['site_product_prices_visible'] ?? 'true') === 'true';
        // Van-e egyáltalán választható szállítási mód (ajánlatkérésnél nincs)
        $wsShippingEnabled = !$isQuoteMode && !empty($shippingMethods);
        // Kér-e a pénztár szállítási címet (külön doboz a szállítási mód alatt)
        $wsAddressEnabled = ($ws['site_checkout_shipping_enabled'] ?? 'false') === 'true';
    @endphp

    <div class="ws-page-container ws-checkout-index">
        <div class="container-xl container-fluid pb-5 mt-5">

            @include('site.webshop.checkout.partials.alerts')
            @include('site.webshop.checkout.partials.account-notice')

            <div class="row">
                <div class="col-lg-12">
                    <h1 class="mb-4 font-weight-bold border-bottom pb-2">
                        <i class="fa {{ ($ws['site_checkout_mode'] ?? 'order') == 'quote' ? 'fa-file-invoice' : 'fa-cart-check' }} mr-2"></i>
                        {{ ($ws['site_checkout_mode'] ?? 'order') == 'quote' ? __('webshop::checkout.page.quote_title') : __('webshop::checkout.page.heading') }}
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

            <form method="POST" action="{{ WsUrl::route('site.webshop.checkout.store') }}" class="row">
                @csrf

                {{-- Dobozok: balra a személyes adatok és a szállítási mód, jobbra a szállítási cím, alatta a
                     számlázási adatok és a fizetés. Mobilon ugyanez a kitöltési sorrend.
                     Minden doboz csak ALSÓ margót kap (mb-4), az oszlop nem: így ha a szállítási cím
                     doboza elrejtődik (személyes átvétel, csomagpont), a számlázási doboz pontosan az
                     oszlop tetején kezdődik, a két oszlop teteje egy vonalban marad. --}}
                <div class="col-lg-6">
                    {{-- 2. Személyes adatok --}}
                    @include('site.webshop.checkout.partials.personal-data')

                    {{-- 3. Szállítási mód --}}
                    @if($wsShippingEnabled)
                        @include('site.webshop.checkout.partials.shipping')
                    @endif
                </div>

                <div class="col-lg-6">
                    {{-- 4. Szállítási cím, alatta a számlázási adatok („Megegyezik a szállítással”) --}}
                    @if($wsAddressEnabled)
                        @include('site.webshop.checkout.partials.shipping-address')
                    @endif
                    @if(($ws['site_checkout_billing_enabled'] ?? 'false') === 'true')
                        @include('site.webshop.checkout.partials.billing')
                    @endif

                    {{-- 5. Fizetési lehetőségek --}}
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

                {{-- 5. Kuponbeváltás és Megjegyzés.
                     Ha van beváltható kupon, a két doboz egy sorban áll: a kupon
                     balra, a megjegyzés mellette – ilyenkor mindkettő col-lg-6.
                     Kupon nélkül a megjegyzés a teljes szélességet kapja. --}}
                @if($couponAvailable ?? false)
                    <div class="col-lg-6 mb-4">
                        @include('site.webshop.checkout.partials.coupon')
                    </div>
                @endif

                <div class="{{ ($couponAvailable ?? false) ? 'col-lg-6' : 'col-12' }} mb-4">
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
                <div class="col-lg-6 mx-auto">
                    @include('site.webshop.checkout.partials.summary')
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        @include('site.webshop.checkout.partials.scripts')
    @endpush
@endsection
