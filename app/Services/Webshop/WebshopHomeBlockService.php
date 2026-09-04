<?php

namespace Weboldalnet\WebshopAiDefault\Services\Webshop;

use Weboldalnet\WebshopAiDefault\Models\WebshopCategory;
use Weboldalnet\WebshopAiDefault\Models\WebshopHomeBlock;
use Weboldalnet\WebshopAiDefault\Models\WebshopProduct;

/**
 * A nyitóoldali blokkok adatainak feltöltése.
 *
 * MINDEN lekérdezés itt fut, nem a blade-ben: így a termékek és kategóriák
 * blokkonként EGY lekérdezéssel jönnek, a kapcsolatok előre betöltve.
 * Blade-ből lekérdezve egy 4 blokkos oldal is könnyen több tucat query lenne.
 */
class WebshopHomeBlockService
{
    /**
     * A site-oldali megjelenítéshez: csak aktív blokkok, feltöltött tartalommal.
     *
     * @return array<int, array{block: WebshopHomeBlock, items: iterable}>
     */
    public static function hydrateForSite(): array
    {
        $blokkok = WebshopHomeBlock::active()->ordered()->get();

        return $blokkok->map(function (WebshopHomeBlock $blokk) {
            return [
                'block' => $blokk,
                'items' => self::items($blokk, false),
            ];
        })->all();
    }

    /**
     * Az admin szerkesztőhöz: minden blokk, és a hivatkozott elemek úgy, hogy
     * a KÖZBEN TÖRÖLT elemek is látszanak (helyőrzőként), nem tűnnek el némán.
     *
     * @return array<int, array{block: WebshopHomeBlock, items: iterable}>
     */
    public static function hydrateForAdmin(): array
    {
        $blokkok = WebshopHomeBlock::ordered()->get();

        return $blokkok->map(function (WebshopHomeBlock $blokk) {
            return [
                'block' => $blokk,
                'items' => self::items($blokk, true),
            ];
        })->all();
    }

    /**
     * Egy blokk tartalma.
     *
     * @param bool $adminMod törölt elemeknél helyőrzőt adjon-e vissza
     */
    protected static function items(WebshopHomeBlock $blokk, bool $adminMod): array
    {
        switch ($blokk->type) {
            case WebshopHomeBlock::TYPE_PRODUCTS_MANUAL:
                return self::manualProducts($blokk, $adminMod);

            case WebshopHomeBlock::TYPE_PRODUCTS_CATEGORY:
                return self::categoryProducts($blokk);

            case WebshopHomeBlock::TYPE_CATEGORIES:
                return self::categories($blokk, $adminMod);

            default:
                return [];
        }
    }

    /**
     * Kézzel válogatott termékek — a MENTETT SORRENDBEN.
     *
     * A whereIn az adatbázis sorrendjében ad vissza, ezért utólag rendezzük
     * vissza az azonosítólista szerint; különben az admin drag&drop sorrendje
     * a site-on nem látszana.
     */
    protected static function manualProducts(WebshopHomeBlock $blokk, bool $adminMod): array
    {
        $idk = $blokk->setting('product_ids', []);
        if (empty($idk)) {
            return [];
        }

        $termekek = WebshopProduct::query()
            ->whereIn('id', $idk)
            ->with(['category', 'label'])
            ->get()
            ->keyBy('id');

        $eredmeny = [];
        foreach ($idk as $id) {
            if (isset($termekek[$id])) {
                $eredmeny[] = $termekek[$id];
            } elseif ($adminMod) {
                // Az adminban látszania kell, hogy egy hivatkozott termék eltűnt
                $eredmeny[] = ['missing' => true, 'id' => $id];
            }
        }

        return $eredmeny;
    }

    /**
     * Egy kategória termékei, a blokkon beállított rendezéssel és darabszámmal.
     */
    protected static function categoryProducts(WebshopHomeBlock $blokk): array
    {
        $kategoriaId = $blokk->setting('category_id');
        if (!$kategoriaId) {
            return [];
        }

        $query = WebshopProduct::query()
            ->where('category_id', $kategoriaId)
            ->with(['category', 'label']);

        if (method_exists(WebshopProduct::class, 'scopeActive')) {
            $query->active();
        }

        switch ($blokk->setting('sort', 'sort_order')) {
            case 'newest':
                $query->orderByDesc('id');
                break;
            case 'price_asc':
                $query->orderBy('price');
                break;
            case 'price_desc':
                $query->orderByDesc('price');
                break;
            case 'name_asc':
                $query->orderBy('name');
                break;
            default:
                $query->orderBy('sort_order')->orderBy('id');
        }

        return $query->limit((int) $blokk->setting('limit', 8))->get()->all();
    }

    /**
     * Kiválasztott kategóriák — szintén a mentett sorrendben.
     */
    protected static function categories(WebshopHomeBlock $blokk, bool $adminMod): array
    {
        $idk = $blokk->setting('category_ids', []);
        if (empty($idk)) {
            return [];
        }

        $kategoriak = WebshopCategory::query()
            ->whereIn('id', $idk)
            ->get()
            ->keyBy('id');

        $eredmeny = [];
        foreach ($idk as $id) {
            if (isset($kategoriak[$id])) {
                $eredmeny[] = $kategoriak[$id];
            } elseif ($adminMod) {
                $eredmeny[] = ['missing' => true, 'id' => $id];
            }
        }

        return $eredmeny;
    }

    /**
     * Van-e egyáltalán megjeleníthető nyitóoldal?
     *
     * A főkapcsoló önmagában nem elég: aktív blokk nélkül üres lapot adnánk,
     * ezért ilyenkor a kategórialista marad.
     */
    public static function isHomePageActive(): bool
    {
        if (!WebshopSettingsService::getBool('site_home_page_enabled')) {
            return false;
        }

        return WebshopHomeBlock::active()->exists();
    }
}
