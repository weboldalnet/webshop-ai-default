<?php

namespace Weboldalnet\WebshopAiDefault\Services\Webshop;

use Illuminate\Support\Facades\Session;
use Weboldalnet\WebshopAiDefault\Models\WebshopProduct;

class WebshopCompareService
{
    private const SESSION_KEY = 'webshop_compare';

    public static function add(int $productId): array
    {
        $compare = self::getContent();
        
        if (isset($compare[$productId])) {
            return ['success' => true, 'message' => 'A termék már szerepel az összehasonlításban.'];
        }

        $product = WebshopProduct::active()->find($productId);
        if (!$product) {
            return ['success' => false, 'message' => 'A termék nem található.'];
        }

        // Kategória ellenőrzés
        if (!empty($compare)) {
            $firstProduct = reset($compare);
            if ($firstProduct['category_id'] !== $product->category_id) {
                return ['success' => false, 'message' => 'Csak azonos kategóriában lévő termékeket lehet összehasonlítani.'];
            }
        }

        $compare[$productId] = [
            'product_id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'category_id' => $product->category_id,
            'category_name' => $product->category->name_singular ?? '',
        ];

        self::save($compare);
        return ['success' => true, 'message' => 'Termék hozzáadva az összehasonlításhoz.'];
    }

    public static function remove(int $productId): void
    {
        $compare = self::getContent();
        unset($compare[$productId]);
        self::save($compare);
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
        return count(self::getContent());
    }

    private static function save(array $compare): void
    {
        Session::put(self::SESSION_KEY, $compare);
    }
}
