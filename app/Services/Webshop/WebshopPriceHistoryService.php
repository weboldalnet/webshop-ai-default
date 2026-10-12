<?php

namespace Weboldalnet\WebshopAiDefault\Services\Webshop;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Weboldalnet\WebshopAiDefault\Models\WebshopProduct;

/**
 * Ártörténet és az akciós „korábbi ár” (uniós Omnibusz-irányelv).
 *
 * Árcsökkentés hirdetésekor korábbi árként az akciót megelőző 30 nap legalacsonyabb
 * eladási árát kell feltüntetni – nem egy tetszőleges „eredeti” árat. A WebshopProduct
 * mentési eseményei hívják:
 *  - syncSaleReference(): mentés előtt, akció indulásakor rögzíti a referenciaárat;
 *  - record(): mentés után naplózza az eladási ár változását.
 *
 * Lépcsőzetes akciónál (az akciós ár tovább csökken) a referencia az első csökkentés
 * előtti ár marad, ahogy az irányelv megengedi.
 */
class WebshopPriceHistoryService
{
    public const TABLE = 'public.webshop_product_price_history';

    public const WINDOW_DAYS = 30;

    private static ?bool $enabled = null;

    /**
     * Lefutott-e már a migráció. Telepítés közben (migrálás előtt) a termékmentés ne álljon le.
     */
    public static function enabled(): bool
    {
        if (self::$enabled === null) {
            try {
                self::$enabled = Schema::hasTable(self::TABLE)
                    && Schema::hasColumn('public.webshop_products', 'sale_reference_price');
            } catch (\Throwable $e) {
                self::$enabled = false;
            }
        }

        return self::$enabled;
    }

    /**
     * A vásárló által fizetendő ár a megadott ár-mezőkből.
     */
    public static function sellingPrice($price, $salePrice): ?float
    {
        if ($salePrice !== null && (float) $salePrice > 0) {
            return (float) $salePrice;
        }

        return $price !== null ? (float) $price : null;
    }

    /**
     * Az adott időpontot megelőző 30 nap legalacsonyabb eladási ára a napló alapján.
     * Az ablak elején érvényes ár (az ablak előtti utolsó bejegyzés) is beleszámít.
     */
    public static function lowestPriorPrice(int $productId, ?Carbon $before = null): ?float
    {
        if (!self::enabled()) {
            return null;
        }

        $before = $before ?: Carbon::now();
        $windowStart = $before->copy()->subDays(self::WINDOW_DAYS);

        $inWindow = DB::table(self::TABLE)
            ->where('product_id', $productId)
            ->where('recorded_at', '>=', $windowStart)
            // <=: az időbélyeg másodperc pontosságú, az azonos másodpercben rögzített ár is „korábbi”
            ->where('recorded_at', '<=', $before)
            ->min('price');

        $atWindowStart = DB::table(self::TABLE)
            ->where('product_id', $productId)
            ->where('recorded_at', '<', $windowStart)
            ->orderByDesc('recorded_at')
            ->orderByDesc('id')
            ->value('price');

        $candidates = array_filter([$inWindow, $atWindowStart], fn ($value) => $value !== null);

        return $candidates ? (float) min($candidates) : null;
    }

    /**
     * Mentés előtt: akció indulásakor rögzíti a referenciaárat, akció végén törli.
     */
    public static function syncSaleReference(WebshopProduct $product): void
    {
        if (!self::enabled()) {
            return;
        }

        $saleActive = $product->sale_price !== null && (float) $product->sale_price > 0;

        if (!$saleActive) {
            $product->sale_reference_price = null;
            $product->sale_started_at = null;
            return;
        }

        $originalSale = $product->getOriginal('sale_price');
        $wasOnSale = $product->exists && $originalSale !== null && (float) $originalSale > 0;

        // Futó akció (akár lépcsőzetesen csökkentett): a referencia marad
        if ($wasOnSale && $product->sale_reference_price !== null) {
            return;
        }

        $reference = $product->exists ? self::lowestPriorPrice($product->id) : null;

        if ($reference === null) {
            // Nincs még napló (új termék vagy friss telepítés): a mentés előtti eladási ár
            $reference = $product->exists
                ? self::sellingPrice($product->getOriginal('price'), $originalSale)
                : null;
            $reference = $reference ?? ($product->price !== null ? (float) $product->price : null);
        }

        $product->sale_reference_price = $reference;
        $product->sale_started_at = Carbon::now();
    }

    /**
     * Mentés után: naplózza az eladási árat, ha változott a legutóbbi bejegyzéshez képest.
     */
    public static function record(WebshopProduct $product): void
    {
        if (!self::enabled()) {
            return;
        }

        $price = self::sellingPrice($product->price, $product->sale_price);

        if ($price === null) {
            return;
        }

        $last = DB::table(self::TABLE)
            ->where('product_id', $product->id)
            ->orderByDesc('recorded_at')
            ->orderByDesc('id')
            ->value('price');

        if ($last !== null && abs((float) $last - $price) < 0.005) {
            return;
        }

        DB::table(self::TABLE)->insert([
            'product_id' => $product->id,
            'price' => $price,
            'recorded_at' => Carbon::now(),
        ]);
    }
}
