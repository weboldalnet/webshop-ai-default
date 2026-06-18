<?php

namespace Weboldalnet\WebshopAiDefault\Services\Webshop;

use Weboldalnet\WebshopAiDefault\Models\WebshopCustomContent;
use Weboldalnet\WebshopAiDefault\Models\WebshopOrder;

class WebshopContentService
{
    public static function getContent(string $type, WebshopOrder $order)
    {
        $checkoutMode = WebshopSettingsService::get('site_checkout_mode', 'order');
        $paymentMethod = $order->payment_method;
        $shippingMethod = $order->shipping_method;

        // 1. Checkout mód + Fizetési mód + Szállítási mód (pontos találat)
        $content = WebshopCustomContent::active()->byType($type)
            ->where('checkout_mode', $checkoutMode)
            ->where('payment_method', $paymentMethod)
            ->where('shipping_method', $shippingMethod)
            ->first();

        if ($content) return $content;

        // 2. Checkout mód + Fizetési mód
        $content = WebshopCustomContent::active()->byType($type)
            ->where('checkout_mode', $checkoutMode)
            ->where('payment_method', $paymentMethod)
            ->whereNull('shipping_method')
            ->first();

        if ($content) return $content;

        // 3. Checkout mód + Szállítási mód
        $content = WebshopCustomContent::active()->byType($type)
            ->where('checkout_mode', $checkoutMode)
            ->whereNull('payment_method')
            ->where('shipping_method', $shippingMethod)
            ->first();

        if ($content) return $content;

        // 4. Checkout mód (alapértelmezett szöveg a módhoz)
        $content = WebshopCustomContent::active()->byType($type)
            ->where('checkout_mode', $checkoutMode)
            ->whereNull('payment_method')
            ->whereNull('shipping_method')
            ->first();

        return $content;
    }
}
