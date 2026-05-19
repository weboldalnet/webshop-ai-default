<?php

namespace Weboldalnet\WebshopAiDefault\Services\Webshop;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Weboldalnet\WebshopAiDefault\Models\WebshopOrder;
use Weboldalnet\WebshopAiDefault\Models\WebshopOrderItem;

class WebshopCheckoutService
{
    public function process(array $data, array $cartItems): WebshopOrder
    {
        return DB::transaction(function () use ($data, $cartItems) {
            $totalPrice = 0;
            foreach ($cartItems as $item) {
                $totalPrice += $item['quantity'] * $item['price'];
            }

            $billingData = null;
            if (WebshopSettingsService::getBool('site_checkout_billing_enabled')) {
                if (!empty($data['billing_same_as_shipping'])) {
                    $billingData = [
                        'name' => $data['name'],
                        'zip' => $data['shipping']['zip'] ?? null,
                        'city' => $data['shipping']['city'] ?? null,
                        'address' => $data['shipping']['address'] ?? null,
                    ];
                } else {
                    $billingData = $data['billing'] ?? null;
                }
            }

            $order = WebshopOrder::create([
                'order_number' => $this->generateOrderNumber(),
                'status' => WebshopOrder::STATUS_PENDING,
                'type' => WebshopSettingsService::get('site_checkout_mode', 'order'),
                'customer_name' => $data['name'],
                'customer_email' => $data['email'],
                'customer_phone' => $data['phone'] ?? null,
                'customer_company' => $data['company'] ?? null,
                'customer_tax_number' => $data['tax_number'] ?? null,
                'billing_data' => $billingData ? json_encode($billingData) : null,
                'shipping_data' => isset($data['shipping']) ? json_encode($data['shipping']) : null,
                'total_price' => $totalPrice,
                'currency' => 'HUF',
                'note' => $data['note'] ?? null,
                'is_completed' => false,
            ]);

            foreach ($cartItems as $item) {
                WebshopOrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['name'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'total_price' => $item['quantity'] * $item['price'],
                ]);
            }

            return $order;
        });
    }

    private function generateOrderNumber(): string
    {
        $prefix = date('Ymd');
        $random = strtoupper(Str::random(4));
        $number = $prefix . '-' . $random;

        // Ellenőrizzük az egyediséget
        while (WebshopOrder::where('order_number', $number)->exists()) {
            $random = strtoupper(Str::random(4));
            $number = $prefix . '-' . $random;
        }

        return $number;
    }
}
