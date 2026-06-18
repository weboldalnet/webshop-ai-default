<?php

namespace Weboldalnet\WebshopAiDefault\Services\Webshop\Commerce;

use Weboldalnet\WebshopAiDefault\Models\WebshopOrder;

class WebshopPaymentRequestFactory
{
    public static function fromOrder(WebshopOrder $order, array $options = []): array
    {
        $billingData = $order->billing_data ? json_decode($order->billing_data, true) : [];
        $shippingData = $order->shipping_data ? json_decode($order->shipping_data, true) : [];

        return [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'amount' => $order->total_price,
            'currency' => $order->currency ?? 'HUF',
            'payment_method' => $order->payment_method,
            'customer' => [
                'name' => $order->customer_name,
                'email' => $order->customer_email,
                'phone' => $order->customer_phone,
                'company' => $order->customer_company,
                'tax_number' => $order->customer_tax_number,
            ],
            'billing_address' => [
                'name' => $billingData['name'] ?? $order->customer_name,
                'zip' => $billingData['zip'] ?? null,
                'city' => $billingData['city'] ?? null,
                'address' => $billingData['address'] ?? null,
                'country' => $billingData['country'] ?? 'HU',
            ],
            'shipping_address' => [
                'name' => $shippingData['name'] ?? $order->customer_name,
                'zip' => $shippingData['zip'] ?? null,
                'city' => $shippingData['city'] ?? null,
                'address' => $shippingData['address'] ?? null,
                'country' => $shippingData['country'] ?? 'HU',
            ],
            'items' => $order->items->map(function ($item) {
                return [
                    'name' => $item->product_name,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total_price' => $item->total_price,
                ];
            })->toArray(),
            'return_url' => $options['return_url'] ?? route('site.webshop.payment.result', ['order' => $order->id]),
            'callback_url' => $options['callback_url'] ?? url('/commerce/payment/' . $order->payment_method . '/callback'),
            'language' => $options['language'] ?? 'HU',
            'timeout' => $options['timeout'] ?? 1800,
        ];
    }
}
