<?php

namespace Weboldalnet\WebshopAiDefault\Services\Webshop;

use Illuminate\Support\Facades\Session;
use Weboldalnet\WebshopAiDefault\Models\WebshopProduct;

class WebshopCartService
{
    private const SESSION_KEY = 'webshop_cart';

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
        $kertOsszesen = $alreadyInCart + $quantity;
        $engedett = WebshopStockService::acceptableQuantity($product, $kertOsszesen);
        $quantity = max(0, $engedett - $alreadyInCart);

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
                'slug' => $product->slug,
                'quantity' => $quantity,
                'price' => $product->sale_price ?? $product->price ?? 0,
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
        return Session::get(self::SESSION_KEY, []);
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
