<?php

namespace Weboldalnet\WebshopAiDefault\Services\Webshop;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Weboldalnet\WebshopAiDefault\Models\WebshopOrder;
use Weboldalnet\WebshopAiDefault\Models\WebshopOrderItem;
use Weboldalnet\WebshopAiDefault\Services\Webshop\Commerce\WebshopCommerceService;

class WebshopCheckoutService
{
    /**
     * Feldolgozza a checkout adatokat, létrehozza a rendelést és elindítja a payment flow-t.
     *
     * @return array ['order' => WebshopOrder, 'commerceResult' => array]
     */
    public function process(array $data, array $cartItems): array
    {
        return DB::transaction(function () use ($data, $cartItems) {
            // Szerver oldalon újraszámolt összeg (sosem kliensből)
            $itemsTotal = 0;
            foreach ($cartItems as $item) {
                $itemsTotal += $item['quantity'] * $item['price'];
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

            // Checkout mód meghatározása
            $checkoutMode = WebshopSettingsService::get('site_checkout_mode', 'order');
            $isQuoteMode = $checkoutMode === WebshopOrder::TYPE_QUOTE;

            // Fizetési mód
            $paymentMethod = $isQuoteMode ? null : ($data['payment_method'] ?? null);

            // Szállítási mód
            $shippingMethod = $data['shipping_method'] ?? null;

            // Csomagpontos szállításnál a kiválasztott átvevőpont címe a kézbesítési cím.
            // A választó külön névtérben küldi az adatokat, hogy ne ütközzön a
            // vásárló saját szállítási címével.
            $shippingData = $data['shipping'] ?? null;
            $parcelShopCode = config('commerce-gls.parcel_shop_code');
            if ($parcelShopCode && $shippingMethod === $parcelShopCode && !empty($shippingData['parcel_shop_id'])) {
                $shippingData = [
                    'parcel_shop_id' => $shippingData['parcel_shop_id'],
                    'parcel_shop_name' => $shippingData['parcel_shop_name'] ?? null,
                    'name' => $shippingData['parcel_shop_name'] ?? ($data['name'] ?? null),
                    'zip' => $shippingData['parcel_shop_zip'] ?? null,
                    'city' => $shippingData['parcel_shop_city'] ?? null,
                    'address' => $shippingData['parcel_shop_address'] ?? null,
                    'country' => $shippingData['parcel_shop_country'] ?? 'HU',
                ];
            }

            // Kezdeti státuszok
            if ($isQuoteMode) {
                $initialPaymentStatus = WebshopOrder::PAYMENT_STATUS_UNPAID;
                $initialInvoiceStatus = WebshopOrder::INVOICE_STATUS_NOT_REQUIRED;
                $initialShippingStatus = WebshopOrder::SHIPPING_STATUS_NOT_REQUIRED;
            } else {
                $initialPaymentStatus = WebshopOrder::PAYMENT_STATUS_UNPAID;
                $initialInvoiceStatus = WebshopOrder::INVOICE_STATUS_NOT_REQUIRED;
                $initialShippingStatus = $shippingMethod ? WebshopOrder::SHIPPING_STATUS_PENDING : WebshopOrder::SHIPPING_STATUS_NOT_REQUIRED;
            }

            // Szállítási díj: a providertől kérdezzük, szintén szerver oldalon.
            // Korábban ez sehol nem történt meg, így a díj sosem került a végösszegbe.
            $shippingCost = WebshopCommerceService::calculateShippingCost($shippingMethod, $itemsTotal);
            $totalPrice = $itemsTotal + $shippingCost;

            $order = WebshopOrder::create([
                'order_number' => $this->generateOrderNumber(),
                'status' => WebshopOrder::STATUS_PENDING,
                'type' => $checkoutMode,
                'customer_name' => $data['name'],
                'customer_email' => $data['email'],
                'customer_phone' => $data['phone'] ?? null,
                'customer_company' => $data['company'] ?? null,
                'customer_tax_number' => $data['tax_number'] ?? null,
                // A modellen 'array' cast van, tehát tömbként adjuk át (a json_encode dupla kódolást okozna).
                'billing_data' => $billingData ?: null,
                'shipping_data' => $shippingData,
                'total_price' => $totalPrice,
                'shipping_cost' => $shippingCost,
                'currency' => 'HUF',
                'note' => $data['note'] ?? null,
                'is_completed' => false,
                'payment_method' => $paymentMethod,
                'payment_status' => $initialPaymentStatus,
                'invoice_status' => $initialInvoiceStatus,
                'shipping_method' => $shippingMethod,
                'shipping_status' => $initialShippingStatus,
            ]);

            foreach ($cartItems as $item) {
                WebshopOrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['name'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'total_price' => $item['quantity'] * $item['price'],
                    // A rendeléskori súly rögzítése (a futárszolgálati címkéhez kell)
                    'weight' => $item['weight'] ?? null,
                ]);
            }

            // Commerce-core flow indítása ajánlatkérési módban nem szükséges
            if ($isQuoteMode || !$paymentMethod) {
                return ['order' => $order, 'commerceResult' => null];
            }

            // Payment flow indítása
            $order->load('items');
            $commerceResult = WebshopCommerceService::processOrderPayment($order);

            // Payment státusz frissítése az eredmény alapján
            $paymentStatus = $commerceResult['paymentStatus'] ?? WebshopOrder::PAYMENT_STATUS_PENDING;
            $orderStatus = $commerceResult['success']
                ? ($commerceResult['orderStatusSuggestion'] ?? WebshopOrder::STATUS_PROCESSING)
                : WebshopOrder::STATUS_PENDING;

            $updateData = [
                'payment_status' => $paymentStatus,
                'status' => $orderStatus,
            ];
            if (!empty($commerceResult['transactionId'])) {
                $updateData['commerce_payment_transaction_id'] = $commerceResult['transactionId'];
            }
            $order->update($updateData);
            $order->refresh();

            return ['order' => $order, 'commerceResult' => $commerceResult];
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
