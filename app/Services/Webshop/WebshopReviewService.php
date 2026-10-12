<?php

namespace Weboldalnet\WebshopAiDefault\Services\Webshop;

use Weboldalnet\WebshopAiDefault\Models\WebshopOrder;
use Weboldalnet\WebshopAiDefault\Models\WebshopProduct;

/**
 * Vásárlói vélemények: moderálás és igazolt vásárlás.
 *
 * Ha a bolt véleményeket mutat, tájékoztatnia kell a vevőket, hogy ellenőrzi-e –
 * és ha igen, hogyan –, hogy a vélemények a terméket ténylegesen megvásárló
 * vevőktől származnak (Fttv., uniós Omnibusz-szabályok). A tájékoztató szöveget
 * ezért a TÉNYLEGES beállításból állítjuk elő (disclosureText), nem kézzel írjuk:
 * így nem állíthat többet, mint amit a rendszer valóban ellenőriz.
 *
 * Két, egymástól független beállítás:
 *  - moderálás (alapból BE): az új vélemény csak admin jóváhagyás után jelenik meg;
 *  - csak igazolt vásárlók (alapból KI): véleményt csak az írhat, aki rendelésszámmal
 *    és e-mail-címmel igazolja, hogy a terméket (vagy egy változatát) itt vásárolta.
 * Igazolást a második nélkül is lehet adni: akkor a vélemény „Ellenőrzött vásárló”
 * jelölést kap.
 */
class WebshopReviewService
{
    public const MODERATION_KEY = 'site_product_reviews_moderation';
    public const VERIFIED_ONLY_KEY = 'site_product_reviews_verified_only';

    public static function moderationEnabled(): bool
    {
        return WebshopSettingsService::getBool(self::MODERATION_KEY, true);
    }

    public static function verifiedOnly(): bool
    {
        return WebshopSettingsService::getBool(self::VERIFIED_ONLY_KEY, false);
    }

    /**
     * A vásárlást igazoló rendelés: a rendelésszám és az e-mail-cím együtt egyezik,
     * és a rendelésben szerepel a termék vagy annak egy változata.
     */
    public static function findPurchaseOrder(int $productId, ?string $orderNumber, ?string $email): ?WebshopOrder
    {
        if (trim((string) $orderNumber) === '' || trim((string) $email) === '') {
            return null;
        }

        $order = WebshopWithdrawalService::findOrderByNumber($orderNumber);

        if (!$order || !WebshopWithdrawalService::emailMatches($order, $email)) {
            return null;
        }

        $productIds = self::productAndVariationIds($productId);

        return $order->items->contains(fn ($item) => in_array((int) $item->product_id, $productIds, true))
            ? $order
            : null;
    }

    /**
     * A termék és változatainak azonosítói – a termékoldal a csoport képviselőjét
     * mutatja, a vevő viszont bármelyik változatot vehette.
     */
    private static function productAndVariationIds(int $productId): array
    {
        $product = WebshopProduct::find($productId);

        if (!$product) {
            return [$productId];
        }

        // get()->pluck(): a tábla sémaelőtaggal (public.) van megadva, a minősített
        // oszlopnevű pluck ezért adatbázisonként eltérően viselkedhetne.
        return $product->variations()->get()->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->push($productId)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * A termékoldalon megjelenő tájékoztatás a vélemények eredetéről és kezeléséről.
     */
    public static function disclosureText(): string
    {
        $parts = [];

        if (self::verifiedOnly()) {
            $parts[] = 'Véleményt csak a terméket nálunk megvásárló vevők írhatnak: ezt a rendelésszám és az e-mail-cím alapján ellenőrizzük.';
        } else {
            $parts[] = 'Az „Ellenőrzött vásárló” jelölésű véleményeknél a véleményező a rendelésszámával igazolta, hogy a terméket nálunk vásárolta. A jelölés nélküli véleményeknél a vásárlást nem ellenőrizzük.';
        }

        if (self::moderationEnabled()) {
            $parts[] = 'A véleményeket közzététel előtt átnézzük.';
        }

        return implode(' ', $parts);
    }
}
