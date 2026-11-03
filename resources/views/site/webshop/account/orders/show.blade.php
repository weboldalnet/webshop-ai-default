{{-- Egy rendelés a vásárlói fiókban: a visszaigazoló oldal címek / tételek / extra részleteivel. --}}
@extends('commerce-users::site.layouts.account')
@section('title', __('webshop::account.order.title', ['number' => $order->order_number]))
@section('cu_title', __('webshop::account.order.title', ['number' => $order->order_number]))

@section('cu_content')
    @php
        $showPrices = \Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopSettingsService::getBool('site_product_prices_visible', true);
    @endphp

    <div class="cu-card">
        <div class="d-flex flex-wrap justify-content-between align-items-start" style="gap: 12px 24px">
            <dl class="cu-data mb-0">
                <dt>{{ __('webshop::account.order.placed_at') }}</dt>
                <dd>{{ WsFormat::datetime($order->created_at) }}</dd>
                <dt>{{ __('webshop::account.order.status') }}</dt>
                <dd>{{ $order->status_label }}</dd>
                @if($order->payment_status && $order->type !== \Weboldalnet\WebshopAiDefault\Models\WebshopOrder::TYPE_QUOTE)
                    <dt>{{ __('webshop::account.order.payment') }}</dt>
                    <dd>{{ $order->payment_status_label }}</dd>
                @endif
                @if($order->shipping_status)
                    <dt>{{ __('webshop::account.order.shipping') }}</dt>
                    <dd>{{ $order->shipping_status_label }}</dd>
                @endif
            </dl>
            <div class="d-flex flex-wrap" style="gap: 8px">
                @if($reorderEnabled && $order->items->isNotEmpty())
                    <form method="POST" action="{{ WsUrl::route('site.webshop.account.orders.reorder', $order->id) }}" class="m-0">
                        @csrf
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-rotate-right"></i> {{ __('webshop::account.reorder.button') }}</button>
                    </form>
                @endif
                <a href="{{ WsUrl::route('site.webshop.account.orders') }}" class="btn btn-outline-secondary">{{ __('webshop::common.back') }}</a>
            </div>
        </div>
        @if($reorderEnabled)
            <span class="cu-hint mt-3">{{ __('webshop::account.reorder.hint') }}</span>
        @endif
    </div>

    <div class="ws-account-order-details">
        @include('site.webshop.checkout.partials.order-addresses')
        @include('site.webshop.checkout.partials.order-items')
        @include('site.webshop.checkout.partials.order-extras')
    </div>
@endsection
