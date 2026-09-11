<?php

namespace Weboldalnet\WebshopAiDefault\Services\Webshop;

use Illuminate\Support\Facades\DB;
use Weboldalnet\WebshopAiDefault\Models\WebshopOrder;
use Weboldalnet\WebshopAiDefault\Models\WebshopProduct;

/**
 * Készletkezelés.
 *
 * A készlet KÉT kapcsolón múlik, és mindkettőnek állnia kell:
 *   - product_stock_enabled  : bolt szintű főkapcsoló (Webshop beállítások)
 *   - a terméken stock_enabled: terméknként külön (van, amit nem készletezünk)
 * Ha bármelyik ki van kapcsolva, a termék korlátlan mennyiségben rendelhető.
 *
 * MIKOR CSÖKKEN: a rendelés LEADÁSAKOR, a rendelést létrehozó tranzakción belül.
 * Nem a fizetés után, mert utánvétnél és előre utalásnál nincs fizetési lépés,
 * és két vásárló ugyanazt az utolsó darabot vihetné el. A "foglalás a leadáskor"
 * a bevett gyakorlat: inkább egy törölt rendelés adja vissza a készletet, mint
 * hogy nem létező árut adjunk el.
 *
 * VERSENYHELYZET: a levonás SELECT ... FOR UPDATE sorzárolással megy, így két
 * egyidejű vásárló közül a második már a csökkentett készletet látja, és ha nem
 * fér bele, a rendelése elutasításra kerül a tranzakció visszagörgetésével.
 *
 * DUPLA LEVONÁS/VISSZAÍRÁS ellen a rendelésen egy stock_applied jelző áll: a
 * levonás csak akkor fut, ha még nem futott, a visszaírás pedig csak akkor, ha
 * volt mit visszaírni.
 */
class WebshopStockService
{
    /** Bolt szintű főkapcsoló */
    public static function managementEnabled(): bool
    {
        return WebshopSettingsService::getBool('product_stock_enabled');
    }

    /** Erre a termékre vonatkozik-e egyáltalán készletkorlát */
    public static function isLimited(?WebshopProduct $product): bool
    {
        return $product
            && self::managementEnabled()
            && (bool) $product->stock_enabled;
    }

    /**
     * Az elérhető darabszám, vagy null, ha korlátlan.
     * A negatív készletet 0-nak vesszük: egy adminban elrontott érték ne
     * eredményezzen "mínusz darab elérhető" jellegű képtelenséget.
     */
    public static function available(?WebshopProduct $product): ?int
    {
        if (!self::isLimited($product)) {
            return null;
        }

        return max(0, (int) $product->stock_quantity);
    }

    /** Elfogyott-e (korlátlan terméknél sosem) */
    public static function isOutOfStock(?WebshopProduct $product): bool
    {
        $available = self::available($product);

        return $available !== null && $available <= 0;
    }

    /**
     * A kosárba ténylegesen betehető mennyiség.
     * A kért mennyiséget a készlethez vágja, nem utasítja el – a vásárlónak
     * hasznosabb, ha a maradékot megkapja, mint ha semmit.
     */
    public static function acceptableQuantity(?WebshopProduct $product, int $requestedQuantity): int
    {
        $available = self::available($product);

        if ($available === null) {
            return max(0, $requestedQuantity);
        }

        return max(0, min($requestedQuantity, $available));
    }

    /**
     * Készlet levonása a rendelés tételei alapján.
     *
     * A HÍVÓ tranzakcióján belül kell futnia (a checkout már tranzakcióban van),
     * hogy a sorzárolás a rendelés létrehozásával együtt érvényesüljön.
     *
     * @throws \RuntimeException ha időközben elfogyott a készlet
     */
    public static function reserve(WebshopOrder $order): void
    {
        if (!self::managementEnabled() || $order->stock_applied) {
            return;
        }

        foreach ($order->items as $item) {
            if (!$item->product_id) {
                continue;
            }

            // Sorzárolás: innentől más tranzakció nem módosíthatja ezt a terméket
            $product = WebshopProduct::whereKey($item->product_id)->lockForUpdate()->first();

            if (!self::isLimited($product)) {
                continue;
            }

            $stock = (int) $product->stock_quantity;
            $needed = (int) $item->quantity;

            if ($stock < $needed) {
                throw new \RuntimeException(
                    'A(z) „' . $product->name . '” termékből időközben csak ' . max(0, $stock)
                    . ' db maradt. Kérjük, módosítsd a kosarad tartalmát.'
                );
            }

            $product->update(['stock_quantity' => $stock - $needed]);
        }

        $order->update(['stock_applied' => true]);
    }

    /**
     * Készlet visszaírása (rendelés törlése / meghiúsulása).
     * Csak akkor fut, ha a levonás korábban ténylegesen megtörtént.
     */
    public static function restore(WebshopOrder $order): void
    {
        if (!$order->stock_applied) {
            return;
        }

        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                if (!$item->product_id) {
                    continue;
                }

                $product = WebshopProduct::whereKey($item->product_id)->lockForUpdate()->first();

                // A visszaírásnál NEM nézzük a stock_enabled-et: ha a levonáskor
                // készletezett volt, a darabokat akkor is vissza kell adni, ha
                // azóta kikapcsolták rajta a készletezést.
                if (!$product) {
                    continue;
                }

                $product->update(['stock_quantity' => (int) $product->stock_quantity + (int) $item->quantity]);
            }

            $order->update(['stock_applied' => false]);
        });
    }

    /**
     * A kosár egésze belefér-e a készletbe.
     * A pénztár a rendelés létrehozása ELŐTT ezzel néz rá, hogy a vásárló
     * érthető hibaüzenetet kapjon, ne egy visszagörgetett tranzakciót.
     *
     * @return array üres, ha minden rendben; különben terméknév => elérhető db
     */
    public static function checkCart(array $cartRows): array
    {
        if (!self::managementEnabled()) {
            return [];
        }

        $errors = [];

        foreach ($cartRows as $rowKey => $row) {
            $product = WebshopProduct::find($row['product_id'] ?? $rowKey);

            if (!self::isLimited($product)) {
                continue;
            }

            $available = self::available($product);

            if ($available < (int) ($row['quantity'] ?? 0)) {
                $errors[$product->name] = $available;
            }
        }

        return $errors;
    }
}
