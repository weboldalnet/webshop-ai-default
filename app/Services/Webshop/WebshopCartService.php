<?php

namespace Weboldalnet\WebshopAiDefault\Services\Webshop;

use Illuminate\Support\Facades\Session;
use Weboldalnet\WebshopAiDefault\Models\WebshopProduct;

class WebshopCartService
{
    private const SESSION_KEY = 'webshop_cart';

    /** Kérésen belüli gyorsítótár a tételnevekhez a kérés nyelvén: [kulcs => [termék-id => [name, sec_name]]] */
    private static array $localizedNames = [];

    /**
     * @return int a ténylegesen kosárba tett mennyiség (készlethiánynál kevesebb lehet)
     */
    public static function add(int $productId, int $quantity = 1): int
    {
        $cart = self::getContent();
        $product = WebshopProduct::active()->find($productId);

        // Készlethatár: a MÁR kosárban lévő mennyiséggel együtt nézzük, különben
        // többszöri "Kosárba" kattintással túl lehetne lépni a raktárkészletet.
        $alreadyInCart = (int) ($cart[$productId]['quantity'] ?? 0);
        $requestedTotal = $alreadyInCart + $quantity;
        $allowedQuantity = WebshopStockService::acceptableQuantity($product, $requestedTotal);
        $quantity = max(0, $allowedQuantity - $alreadyInCart);

        if ($quantity <= 0) {
            return 0;
        }

        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] += $quantity;
        } else {
            $product = $product;
            if (!$product) return 0;

            $cart[$productId] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'sec_name' => $product->secondary_name,
                // az eredeti slug: a cím nyelvét a megjelenítés dönti el (WsUrl::productById)
                'slug' => $product->untranslated('slug'),
                'quantity' => $quantity,
                'price' => ($product->sellingPrice() ?? 0),
                'image' => $product->primary_image,
                'image_thumb' => $product->primary_image_thumb,
                'category_id' => $product->category_id,
                'weight' => $product->weight,
            ];
        }

        self::save($cart);

        return $quantity;
    }

    /**
     * @return int a beallitott mennyiseg (keszlethianynal kevesebb lehet)
     */
    public static function update(int $productId, int $quantity): int
    {
        $cart = self::getContent();

        if (!isset($cart[$productId])) {
            return 0;
        }

        if ($quantity <= 0) {
            self::remove($productId);
            return 0;
        }

        // Keszlethatar: tobbet nem lehet beallitani, mint amennyi raktaron van
        $quantity = WebshopStockService::acceptableQuantity(WebshopProduct::find($productId), $quantity);

        if ($quantity <= 0) {
            self::remove($productId);
            return 0;
        }

        $cart[$productId]['quantity'] = $quantity;
        self::save($cart);

        return $quantity;
    }

    public static function remove(int $productId): void
    {
        $cart = self::getContent();
        unset($cart[$productId]);
        self::save($cart);
    }

    public static function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    public static function getContent(): array
    {
        return self::localized(Session::get(self::SESSION_KEY, []));
    }

    /**
     * A tételek neve a kérés nyelvén. A munkamenetben a hozzáadáskori név van, többnyelvű site-on viszont a vásárló
     * közben nyelvet válthat — a pénztár és a rendelés tételnevei a rendelés nyelvén legyenek. Egynyelvű projektben
     * nem kérdez le semmit (a tárolt név marad); egyébként kérésenként egyszer.
     */
    private static function localized(array $cart): array
    {
        if (!$cart || !WebshopTranslationService::isMultiLanguage() || app()->runningInConsole()) {
            return $cart;
        }

        $key = app()->getLocale() . '|' . implode(',', array_keys($cart));
        if (!isset(self::$localizedNames[$key])) {
            self::$localizedNames[$key] = WebshopProduct::query()
                ->whereIn('id', array_map('intval', array_keys($cart)))
                ->get()
                ->mapWithKeys(fn ($product) => [$product->id => ['name' => $product->name, 'sec_name' => $product->secondary_name]])
                ->all();
        }

        foreach ($cart as $productId => $item) {
            if (isset(self::$localizedNames[$key][$productId])) {
                $cart[$productId] = array_merge($item, self::$localizedNames[$key][$productId]);
            }
        }

        return $cart;
    }

    public static function getCount(): int
    {
        $count = 0;
        foreach (self::getContent() as $item) {
            $count += $item['quantity'];
        }
        return $count;
    }

    public static function getTotal(): float
    {
        $total = 0;
        foreach (self::getContent() as $item) {
            $total += $item['quantity'] * $item['price'];
        }
        return (float) $total;
    }

    private static function save(array $cart): void
    {
        Session::put(self::SESSION_KEY, $cart);
    }
}
