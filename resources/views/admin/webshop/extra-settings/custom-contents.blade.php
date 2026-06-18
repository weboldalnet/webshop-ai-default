@extends('admin.layouts.layout')
@section('title', $type === 'email' ? 'Email szerkesztése' : 'Köszönjük oldal szerkesztése')

@section('content')
    @include('admin.commons.tinymce-img-upload', ['tinyHeight' => 400])
    <div class="container mt-lg-4 mt-3 mb-150">
        <div class="row">
            <div class="col-lg-12">
                <h2 class="header-box"><i class="fa {{ $type === 'email' ? 'fa-envelope' : 'fa-check-circle' }}"></i> {{ $type === 'email' ? 'Email szerkesztése' : 'Köszönjük oldal szerkesztése' }}</h2>
            </div>
        </div>

        @include('admin.webshop.partials.alerts')

        @php
            $checkoutModes = [
                'order' => 'Rendelés leadása',
                'quote' => 'Ajánlatkérés'
            ];
        @endphp

        @foreach($checkoutModes as $modeCode => $modeLabel)
            @if(($ws['site_checkout_mode'] ?? 'order') === $modeCode || !isset($ws['site_checkout_mode']))
                <div class="row mt-4">
                    <div class="col-lg-12">
                        <h3 class="header-box product-info">{{ $modeLabel }}</h3>
                    </div>
                </div>

                {{-- Alap fallback az adott módhoz --}}
                @include('admin.webshop.extra-settings.partials.content-form', [
                    'type' => $type,
                    'checkout_mode' => $modeCode,
                    'payment_method' => null,
                    'shipping_method' => null,
                    'label' => 'Alapértelmezett szöveg (' . $modeLabel . ')',
                    'content' => $contents->where('checkout_mode', $modeCode)->whereNull('payment_method')->whereNull('shipping_method')->first()
                ])

                @if($modeCode === 'order')
                    {{-- Fizetési módok szerinti --}}
                    @foreach($paymentMethods as $pmCode => $pmLabel)
                        @include('admin.webshop.extra-settings.partials.content-form', [
                            'type' => $type,
                            'checkout_mode' => $modeCode,
                            'payment_method' => $pmCode,
                            'shipping_method' => null,
                            'label' => 'Szöveg ' . $pmLabel . ' esetén',
                            'content' => $contents->where('checkout_mode', $modeCode)->where('payment_method', $pmCode)->whereNull('shipping_method')->first()
                        ])
                    @endforeach

                    {{-- Szállítási módok szerinti --}}
                    @foreach($shippingMethods as $smCode => $smLabel)
                        @include('admin.webshop.extra-settings.partials.content-form', [
                            'type' => $type,
                            'checkout_mode' => $modeCode,
                            'payment_method' => null,
                            'shipping_method' => $smCode,
                            'label' => 'Szöveg ' . $smLabel . ' esetén',
                            'content' => $contents->where('checkout_mode', $modeCode)->whereNull('payment_method')->where('shipping_method', $smCode)->first()
                        ])
                    @endforeach
                @endif
            @endif
        @endforeach

        <div class="admin-save-box" style="left: 0">
            <div class="admin-save-btn-container mx-auto text-center">
                <a href="{{ route('admin.webshop.extra-settings.index') }}" class="btn btn-secondary">Vissza</a>
            </div>
        </div>
    </div>
@endsection
