<?php

namespace Weboldalnet\WebshopAiDefault\Services\Webshop;

use Illuminate\Support\Facades\DB;
use Weboldalnet\WebshopAiDefault\Models\WebshopOrder;
use Weboldalnet\WebshopAiDefault\Models\WebshopWithdrawal;
use Weboldalnet\WebshopAiDefault\Models\WebshopWithdrawalItem;

/**
 * Elállási kérelmek logikája.
 *
 * A site és az admin oldal is innen dolgozik, hogy a "mennyiből lehet még
 * elállni" számítás egyetlen helyen legyen.
 */
class WebshopWithdrawalService
{
    /**
     * Rendelés keresése rendelésszám alapján.
     *
     * Árajánlatra nem lehet elállni, csak valódi rendelésre.
     */
    public static function findOrderByNumber(?string $orderNumber): ?WebshopOrder
    {
        $orderNumber = trim((string) $orderNumber);

        if ($orderNumber === '') {
            return null;
        }

        return WebshopOrder::with('items')
            ->where('type', WebshopOrder::TYPE_ORDER)
            ->whereRaw('UPPER(order_number) = ?', [mb_strtoupper($orderNumber)])
            ->first();
    }

    /**
     * Tételenként mennyi maradt, amitől még el lehet állni.
     *
     * A korábbi kérelmekben szereplő darabszámokat levonjuk, hogy ugyanarra a
     * termékre ne lehessen többször elállni.
     *
     * @return array order_item_id => darab
     */
    public static function remainingQuantities(WebshopOrder $order): array
    {
        $alreadyRequested = WebshopWithdrawalItem::query()
            ->whereIn('withdrawal_id', WebshopWithdrawal::where('order_id', $order->id)->pluck('id'))
            ->selectRaw('order_item_id, SUM(quantity) AS qty')
            ->groupBy('order_item_id')
            ->pluck('qty', 'order_item_id')
            ->toArray();

        $remaining = [];

        foreach ($order->items as $item) {
            $used = (int) ($alreadyRequested[$item->id] ?? 0);
            $remaining[$item->id] = max(0, (int) $item->quantity - $used);
        }

        return $remaining;
    }

    /**
     * Van-e még olyan tétel, amitől el lehet állni?
     */
    public static function hasRemainingItems(WebshopOrder $order): bool
    {
        return array_sum(self::remainingQuantities($order)) > 0;
    }

    /**
     * Elállási kérelem létrehozása.
     *
     * @param array $quantities order_item_id => darab
     */
    public static function create(WebshopOrder $order, array $quantities, string $reason): WebshopWithdrawal
    {
        $remaining = self::remainingQuantities($order);
        $itemsById = $order->items->keyBy('id');

        return DB::transaction(function () use ($order, $quantities, $reason, $remaining, $itemsById) {
            $withdrawal = WebshopWithdrawal::create([
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'customer_name' => $order->customer_name,
                'customer_email' => $order->customer_email,
                'status' => WebshopWithdrawal::STATUS_PENDING,
                'reason' => $reason,
                'is_full' => false,
                'total_amount' => 0,
            ]);

            $total = 0.0;

            foreach ($quantities as $orderItemId => $quantity) {
                $quantity = (int) $quantity;
                $orderItemId = (int) $orderItemId;

                // A kérhető mennyiséget itt is levágjuk – a validáció mellett ez
                // a második védelem a manipulált űrlap ellen.
                $quantity = min($quantity, (int) ($remaining[$orderItemId] ?? 0));

                if ($quantity < 1 || !isset($itemsById[$orderItemId])) {
                    continue;
                }

                $orderItem = $itemsById[$orderItemId];
                $lineTotal = round((float) $orderItem->unit_price * $quantity, 2);
                $total += $lineTotal;

                WebshopWithdrawalItem::create([
                    'withdrawal_id' => $withdrawal->id,
                    'order_item_id' => $orderItem->id,
                    'product_id' => $orderItem->product_id,
                    'product_name' => $orderItem->product_name,
                    'quantity' => $quantity,
                    'unit_price' => (float) $orderItem->unit_price,
                    'total_price' => $lineTotal,
                ]);
            }

            // Teljes rendeléstől áll el, ha minden tétel teljes mennyisége szerepel
            $isFull = true;
            foreach ($order->items as $item) {
                if ((int) ($quantities[$item->id] ?? 0) < (int) $item->quantity) {
                    $isFull = false;
                    break;
                }
            }

            $withdrawal->update([
                'total_amount' => $total,
                'is_full' => $isFull,
            ]);

            return $withdrawal->load('items');
        });
    }
}
