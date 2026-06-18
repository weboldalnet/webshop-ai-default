<?php

namespace Weboldalnet\WebshopAiDefault\Listeners\Commerce;

use Illuminate\Support\Facades\Log;
use Weboldalnet\WebshopAiDefault\Models\WebshopOrder;

class HandlePaymentFailed
{
    public function handle($event): void
    {
        $orderId = $event->orderId ?? null;
        if (!$orderId) {
            return;
        }

        $order = WebshopOrder::find($orderId);
        if (!$order) {
            Log::warning('HandlePaymentFailed: Rendelés nem található.', ['order_id' => $orderId]);
            return;
        }

        // Idempotencia: ha már paid, ne írjuk felül
        if ($order->isPaid()) {
            return;
        }

        $message = $event->message ?? ($event->result->message ?? null);
        Log::warning('HandlePaymentFailed: Fizetés sikertelen.', [
            'order_id' => $orderId,
            'message' => $message,
        ]);

        $order->update([
            'payment_status' => WebshopOrder::PAYMENT_STATUS_FAILED,
            // order_status maradjon, hogy lehetővé tegyük a retry-t
        ]);
    }
}
