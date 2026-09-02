<?php

namespace Weboldalnet\WebshopAiDefault\Services\Webshop\Commerce;

use Weboldalnet\WebshopAiDefault\Models\WebshopOrder;

class WebshopShippingRequestFactory
{
    /** A platform utánvétes fizetési módjának kódja */
    const PAYMENT_METHOD_COD = 'cod';

    public static function fromOrder(WebshopOrder $order, array $options = []): array
    {
        $shippingData = $order->getShippingDataArray();

        // FIGYELEM: a kulcsneveknek egyezniük kell azzal, amit a ShipmentRequestData
        // olvas (customer_name, customer_phone, customer_email, shipping_data, weight).
        return [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'shipping_method' => $order->shipping_method,
            'customer_name' => $order->customer_name,
            'customer_email' => $order->customer_email,
            'customer_phone' => $order->customer_phone,
            'shipping_data' => [
                'name' => $shippingData['name'] ?? $order->customer_name,
                'zip' => $shippingData['zip'] ?? null,
                'city' => $shippingData['city'] ?? null,
                'address' => $shippingData['address'] ?? null,
                'country' => $shippingData['country'] ?? 'HU',
                // Csomagpontos szállításnál a kiválasztott átvevőpont azonosítója
                'parcel_shop_id' => $shippingData['parcel_shop_id'] ?? null,
                'parcel_shop_name' => $shippingData['parcel_shop_name'] ?? null,
            ],
            'items' => $order->items->map(function ($item) {
                return [
                    'name' => $item->product_name,
                    'quantity' => $item->quantity,
                    'weight' => $item->weight ?? null,
                ];
            })->toArray(),
            // A csomag össztömege a tételek rendeléskori súlyából. Ahol nincs
            // megadva, ott a szállítási provider alapértelmezése lép életbe –
            // a futárszolgálati címkéhez kötelező a súly.
            'weight' => $options['total_weight'] ?? self::totalWeight($order),
            'total_price' => $order->total_price,
            'currency' => $order->currency ?? 'HUF',
            'note' => $order->note,
            // Provider-specifikus kiegészítők. Az utánvét összege ezen keresztül
            // jut el a futárszolgálathoz – enélkül a futár nem szedné be a pénzt.
            'extra' => array_merge([
                'cod_amount' => self::codAmount($order),
            ], $options['extra'] ?? []),
        ];
    }

    /**
     * Az utánvéttel beszedendő összeg.
     *
     * Csak utánvétes és még ki nem fizetett rendelésnél van mit beszedni; minden
     * más esetben 0, így a szállítmány utánvét nélkül jön létre.
     */
    protected static function codAmount(WebshopOrder $order): float
    {
        if ($order->payment_method !== self::PAYMENT_METHOD_COD) {
            return 0.0;
        }

        if (method_exists($order, 'isPaid') && $order->isPaid()) {
            return 0.0;
        }

        return (float) $order->total_price;
    }

    /**
     * A rendelés össztömege kilogrammban, a tételek rendeléskori súlyából.
     * Null, ha egyetlen tételnél sincs megadva súly – ilyenkor a provider
     * saját alapértelmezése dönt.
     */
    protected static function totalWeight(WebshopOrder $order): ?float
    {
        $total = 0.0;
        $hasAny = false;

        foreach ($order->items as $item) {
            if ($item->weight !== null && $item->weight > 0) {
                $total += (float) $item->weight * (int) $item->quantity;
                $hasAny = true;
            }
        }

        return $hasAny ? round($total, 3) : null;
    }
}
