<?php

namespace Weboldalnet\WebshopAiDefault\Services\Webshop;

use Illuminate\Support\Facades\Session;
use Weboldalnet\WebshopAiDefault\Models\WebshopProduct;

class WebshopCartService
{
    private const SESSION_KEY = 'webshop_cart';

    public static function add(int $productId, int $quantity = 1): void
    {
        $cart = self::getContent();

        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] += $quantity;
        } else {
            $product = WebshopProduct::active()->find($productId);
            if (!$product) return;

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
            ];
        }

        self::save($cart);
    }

    public static function update(int $productId, int $quantity): void
    {
        $cart = self::getContent();
        if (isset($cart[$productId])) {
            if ($quantity <= 0) {
                self::remove($productId);
            } else {
                $cart[$productId]['quantity'] = $quantity;
                self::save($cart);
            }
        }
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
