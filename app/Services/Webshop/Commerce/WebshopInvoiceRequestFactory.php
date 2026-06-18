<?php

namespace Weboldalnet\WebshopAiDefault\Services\Webshop\Commerce;

use Weboldalnet\WebshopAiDefault\Models\WebshopOrder;

class WebshopInvoiceRequestFactory
{
    public static function fromOrder(WebshopOrder $order, array $options = []): array
    {
        $billingData = $order->billing_data ? json_decode($order->billing_data, true) : [];

        return [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'amount' => $order->total_price,
            'currency' => $order->currency ?? 'HUF',
            'customer' => [
                'name' => $billingData['name'] ?? $order->customer_name,
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
            'items' => $order->items->map(function ($item) {
                return [
                    'name' => $item->product_name,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total_price' => $item->total_price,
                    'vat_rate' => $item->vat_rate ?? 27,
                ];
            })->toArray(),
            'payment_method' => $order->payment_method,
            'paid_at' => $order->paid_at ? $order->paid_at->toDateString() : null,
            'language' => $options['language'] ?? 'HU',
        ];
    }
}
