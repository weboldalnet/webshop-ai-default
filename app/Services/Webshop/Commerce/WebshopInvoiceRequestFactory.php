<?php

namespace Weboldalnet\WebshopAiDefault\Services\Webshop\Commerce;

use Weboldalnet\WebshopAiDefault\Models\WebshopOrder;

class WebshopInvoiceRequestFactory
{
    public static function fromOrder(WebshopOrder $order, array $options = []): array
    {
        $billingData = $order->getBillingDataArray();

        return [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'customer_name' => $billingData['name'] ?? $order->customer_name,
            'customer_email' => $order->customer_email,
            'customer_tax_number' => $order->customer_tax_number,
            'billing_data' => [
                'zip' => $billingData['zip'] ?? null,
                'city' => $billingData['city'] ?? null,
                'address' => $billingData['address'] ?? null,
                'country' => $billingData['country'] ?? 'HU',
            ],
            // A webshop árai bruttók. A nettó/áfa bontást a számlázó provider végzi,
            // mert az áfakerekítés szolgáltatófüggő.
            'items' => $order->items->map(function ($item) {
                return [
                    'name' => $item->product_name,
                    'quantity' => $item->quantity,
                    'unit' => 'db',
                    'gross_unit_price' => (float) $item->unit_price,
                    'gross_total' => (float) $item->total_price,
                    'vat_key' => (string) ($item->vat_rate ?? '27'),
                ];
            })->toArray(),
            'gross_total' => $order->total_price,
            'currency' => $order->currency ?? 'HUF',
            'language' => $options['language'] ?? 'hu',
            'extra' => [
                'issue_date' => date('Y-m-d'),
                'fulfillment_date' => date('Y-m-d'),
                'payment_due_date' => date('Y-m-d', strtotime('+8 days')),
                'payment_method_text' => \Weboldalnet\WebshopAiDefault\Services\Webshop\Commerce\WebshopCommerceService::getPaymentMethodLabel($order->payment_method),
                'comment' => $order->note,
                'is_electronic' => true,
            ],
        ];
    }
}
