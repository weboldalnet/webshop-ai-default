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
        $blocks = WebshopHomeBlock::active()->ordered()->get();

        return $blocks->map(function (WebshopHomeBlock $block) {
            return [
                'block' => $block,
                'items' => self::items($block, false),
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
        $blocks = WebshopHomeBlock::ordered()->get();

        return $blocks->map(function (WebshopHomeBlock $block) {
            return [
                'block' => $block,
                'items' => self::items($block, true),
            ];
        })->all();
    }

    /**
     * Egy blokk tartalma.
     *
     * @param bool $adminMod törölt elemeknél helyőrzőt adjon-e vissza
     */
    protected static function items(WebshopHomeBlock $block, bool $forAdmin): array
    {
        switch ($block->type) {
            case WebshopHomeBlock::TYPE_PRODUCTS_MANUAL:
                return self::manualProducts($block, $forAdmin);

            case WebshopHomeBlock::TYPE_PRODUCTS_CATEGORY:
                return self::categoryProducts($block);

            case WebshopHomeBlock::TYPE_CATEGORIES:
                return self::categories($block, $forAdmin);

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
    protected static function manualProducts(WebshopHomeBlock $block, bool $forAdmin): array
    {
        $ids = $block->setting('product_ids', []);
        if (empty($ids)) {
            return [];
        }

        $products = WebshopProduct::query()
            ->whereIn('id', $ids)
            ->with(['category', 'label'])
            ->get()
            ->keyBy('id');

        $result = [];
        foreach ($ids as $id) {
            if (isset($products[$id])) {
                $result[] = $products[$id];
            } elseif ($forAdmin) {
                // Az adminban látszania kell, hogy egy hivatkozott termék eltűnt
                $result[] = ['missing' => true, 'id' => $id];
            }
        }

        return $result;
    }

    /**
     * Egy kategória termékei, a blokkon beállított rendezéssel és darabszámmal.
     */
    protected static function categoryProducts(WebshopHomeBlock $block): array
    {
        $categoryId = $block->setting('category_id');
        if (!$categoryId) {
            return [];
        }

        $query = WebshopProduct::query()
            ->where('category_id', $categoryId)
            ->with(['category', 'label']);

        if (method_exists(WebshopProduct::class, 'scopeActive')) {
            $query->active();
        }

        switch ($block->setting('sort', 'sort_order')) {
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

        return $query->limit((int) $block->setting('limit', 8))->get()->all();
    }

    /**
     * Kiválasztott kategóriák — szintén a mentett sorrendben.
     */
    protected static function categories(WebshopHomeBlock $block, bool $forAdmin): array
    {
        $ids = $block->setting('category_ids', []);
        if (empty($ids)) {
            return [];
        }

        $categories = WebshopCategory::query()
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');

        $result = [];
        foreach ($ids as $id) {
            if (isset($categories[$id])) {
                $result[] = $categories[$id];
            } elseif ($forAdmin) {
                $result[] = ['missing' => true, 'id' => $id];
            }
        }

        return $result;
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
