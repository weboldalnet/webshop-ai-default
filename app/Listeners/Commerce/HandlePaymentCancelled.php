<?php

namespace Weboldalnet\WebshopAiDefault\Listeners\Commerce;

use Illuminate\Support\Facades\Log;
use Weboldalnet\WebshopAiDefault\Models\WebshopOrder;

class HandlePaymentCancelled
{
    public function handle($event): void
    {
        $orderId = $event->orderId ?? null;
        if (!$orderId) {
            return;
        }

        $order = WebshopOrder::find($orderId);
        if (!$order) {
            Log::warning('HandlePaymentCancelled: Rendelés nem található.', ['order_id' => $orderId]);
            return;
        }

        // Idempotencia: ha már paid, ne írjuk felül
        if ($order->isPaid()) {
            return;
        }

        $order->update([
            'payment_status' => WebshopOrder::PAYMENT_STATUS_CANCELLED,
            // order_status maradjon pending, hogy lehetővé tegyük a retry-t
        ]);
    }
}
