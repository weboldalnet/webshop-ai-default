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

class CreateShipmentForOrder implements ShouldQueue
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
            Log::warning('CreateShipmentForOrder: Rendelés nem található.', ['order_id' => $this->orderId]);
            return;
        }

        // Idempotencia: ha már kiszállítva, skip
        if ($order->isShipped()) {
            return;
        }

        if (!WebshopCommerceService::isAvailable()) {
            Log::warning('CreateShipmentForOrder: Commerce-core nem elérhető.', ['order_id' => $this->orderId]);
            $order->markShippingFailed();
            return;
        }

        try {
            $order->update(['shipping_status' => WebshopOrder::SHIPPING_STATUS_PREPARED]);
            $result = WebshopCommerceService::createShipment($order);

            if ($result['success']) {
                $shipmentId = $result['shipmentId'] ?? null;
                $order->markShipped($shipmentId);

                // Tracking adatok mentése
                if (!empty($result['trackingNumber'])) {
                    $order->update(['shipping_status' => WebshopOrder::SHIPPING_STATUS_SHIPPED]);
                }

                Log::info('CreateShipmentForOrder: Szállítmány sikeresen létrehozva.', [
                    'order_id' => $this->orderId,
                    'tracking_number' => $result['trackingNumber'] ?? null,
                ]);
            } else {
                Log::error('CreateShipmentForOrder: Szállítmányozás sikertelen.', [
                    'order_id' => $this->orderId,
                    'message' => $result['message'] ?? 'Ismeretlen hiba',
                ]);
                if ($this->attempts() >= $this->tries) {
                    $order->markShippingFailed();
                }
            }
        } catch (\Throwable $e) {
            Log::error('CreateShipmentForOrder: Kivétel.', [
                'order_id' => $this->orderId,
                'error' => $e->getMessage(),
            ]);
            if ($this->attempts() >= $this->tries) {
                $order->markShippingFailed();
            }
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        $order = WebshopOrder::find($this->orderId);
        if ($order && !$order->isShipped()) {
            $order->markShippingFailed();
        }
        Log::error('CreateShipmentForOrder: Job véglegesen sikertelen.', [
            'order_id' => $this->orderId,
            'error' => $exception->getMessage(),
        ]);
    }
}
