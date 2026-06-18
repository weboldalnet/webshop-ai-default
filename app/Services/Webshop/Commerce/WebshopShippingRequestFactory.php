<?php

namespace Weboldalnet\WebshopAiDefault\Services\Webshop\Commerce;

use Weboldalnet\WebshopAiDefault\Models\WebshopOrder;

class WebshopShippingRequestFactory
{
    public static function fromOrder(WebshopOrder $order, array $options = []): array
    {
        $shippingData = $order->shipping_data ? json_decode($order->shipping_data, true) : [];

        return [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'shipping_method' => $order->shipping_method,
            'recipient' => [
                'name' => $shippingData['name'] ?? $order->customer_name,
                'email' => $order->customer_email,
                'phone' => $order->customer_phone,
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
                    'weight' => $item->weight ?? null,
                ];
            })->toArray(),
            'total_weight' => $options['total_weight'] ?? null,
            'total_price' => $order->total_price,
            'currency' => $order->currency ?? 'HUF',
            'note' => $order->note,
        ];
    }
}
