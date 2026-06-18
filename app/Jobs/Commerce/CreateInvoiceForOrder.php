<?php

namespace Weboldalnet\WebshopAiDefault\Jobs\Commerce;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Weboldalnet\WebshopAiDefault\Models\WebshopOrder;
use Weboldalnet\WebshopAiDefault\Services\Webshop\Commerce\WebshopCommerceService;

class CreateInvoiceForOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(public readonly int $orderId)
    {
    }

    public function handle(): void
    {
        $order = WebshopOrder::find($this->orderId);
        if (!$order) {
            Log::warning('CreateInvoiceForOrder: Rendelés nem található.', ['order_id' => $this->orderId]);
            return;
        }

        // Idempotencia: ha már számlázva, skip
        if ($order->isInvoiced()) {
            return;
        }

        if (!WebshopCommerceService::isAvailable()) {
            Log::warning('CreateInvoiceForOrder: Commerce-core nem elérhető.', ['order_id' => $this->orderId]);
            $order->markInvoiceFailed();
            return;
        }

        try {
            $result = WebshopCommerceService::createInvoice($order);

            if ($result['success']) {
                $documentId = $result['documentId'] ?? null;
                $order->markInvoiced($documentId);
                Log::info('CreateInvoiceForOrder: Számla sikeresen elkészítve.', [
                    'order_id' => $this->orderId,
                    'invoice_number' => $result['invoiceNumber'] ?? null,
                ]);
            } else {
                Log::error('CreateInvoiceForOrder: Számlázás sikertelen.', [
                    'order_id' => $this->orderId,
                    'message' => $result['message'] ?? 'Ismeretlen hiba',
                ]);
                // Ne frissítsük failed-re, ha még vannak próbálkozások
                if ($this->attempts() >= $this->tries) {
                    $order->markInvoiceFailed();
                }
            }
        } catch (\Throwable $e) {
            Log::error('CreateInvoiceForOrder: Kivétel.', [
                'order_id' => $this->orderId,
                'error' => $e->getMessage(),
            ]);
            if ($this->attempts() >= $this->tries) {
                $order->markInvoiceFailed();
            }
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        $order = WebshopOrder::find($this->orderId);
        if ($order && !$order->isInvoiced()) {
            $order->markInvoiceFailed();
        }
        Log::error('CreateInvoiceForOrder: Job véglegesen sikertelen.', [
            'order_id' => $this->orderId,
            'error' => $exception->getMessage(),
        ]);
    }
}
