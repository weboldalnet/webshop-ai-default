<?php

namespace Weboldalnet\WebshopAiDefault\Listeners\Commerce;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Weboldalnet\WebshopAiDefault\Models\WebshopOrder;
use Weboldalnet\WebshopAiDefault\Jobs\Commerce\CreateInvoiceForOrder;
use Weboldalnet\WebshopAiDefault\Services\Webshop\Commerce\WebshopCommerceService;

class HandlePaymentSucceeded
{
    public function handle($event): void
    {
        $orderId = $event->orderId ?? null;
        if (!$orderId) {
            return;
        }

        $order = WebshopOrder::find($orderId);
        if (!$order) {
            Log::warning('HandlePaymentSucceeded: Rendelés nem található.', ['order_id' => $orderId]);
            return;
        }

        // Idempotencia: ha már paid, ne dolgozzuk fel újra
        if ($order->isPaid()) {
            return;
        }

        DB::transaction(function () use ($order, $event) {
            $transactionId = null;
            if (!empty($event->transaction) && is_object($event->transaction)) {
                $transactionId = $event->transaction->id ?? null;
            } elseif (!empty($event->transactionId)) {
                $transactionId = $event->transactionId;
            }

            $updateData = [
                'payment_status' => WebshopOrder::PAYMENT_STATUS_PAID,
                'status' => WebshopOrder::STATUS_PROCESSING,
                'paid_at' => now(),
            ];
            if ($transactionId) {
                $updateData['commerce_payment_transaction_id'] = $transactionId;
            }

            // Automatikus számlázás indítása, ha be van kapcsolva
            $autoInvoice = $this->shouldAutoInvoice();

            if ($autoInvoice && in_array($order->invoice_status, [WebshopOrder::INVOICE_STATUS_NOT_REQUIRED, WebshopOrder::INVOICE_STATUS_PENDING])) {
                $updateData['invoice_status'] = WebshopOrder::INVOICE_STATUS_PENDING;
            }

            $order->update($updateData);
        });

        // Számlázás job indítása transaction után
        if ($this->shouldAutoInvoice()) {
            $order->refresh();
            if ($order->invoice_status === WebshopOrder::INVOICE_STATUS_PENDING) {
                try {
                    dispatch(new CreateInvoiceForOrder($order->id));
                } catch (\Throwable $e) {
                    Log::error('HandlePaymentSucceeded: Számla job indítása sikertelen.', [
                        'order_id' => $order->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }
    }

    /**
     * Kell-e automatikusan számlát készíteni a fizetés után?
     *
     * A webshop számlázás-főkapcsolója (Webshop beállítások → Számlázás) mindent
     * felülír: ha a számlázás ki van kapcsolva, automatikus számla sem készül.
     */
    private function shouldAutoInvoice(): bool
    {
        if (!WebshopCommerceService::isInvoicingEnabled()) {
            return false;
        }

        if (config('commerce-core.auto_invoice.enabled', false)) {
            return true;
        }

        // A Számlázz.hu csomag saját "automatikus" számlázási módja – csak akkor
        // vesszük figyelembe, ha épp az a kiválasztott számlázó integráció.
        if (WebshopCommerceService::getInvoicingProvider() === 'szamlazzhu'
            && class_exists(\Weboldalnet\CommerceSzamlazzhu\Services\SzamlazzhuSettingsService::class)) {
            return \Weboldalnet\CommerceSzamlazzhu\Services\SzamlazzhuSettingsService::get('billing_mode') === 'automatic';
        }

        return false;
    }
}
