<?php

namespace Weboldalnet\WebshopAiDefault\Services\Webshop;

use Weboldalnet\WebshopAiDefault\Models\WebshopProduct;
use Illuminate\Support\Facades\DB;

class WebshopProductVariationService
{
    public static function syncVariations(WebshopProduct $product, array $variationIds): void
    {
        $variationIds = array_unique(array_filter(array_map('intval', $variationIds), fn($id) => $id !== $product->id));
        $oldVariationIds = $product->variations()->pluck('variation_product_id')->toArray();
        $allOldGroupIds = array_merge([$product->id], $oldVariationIds);
        $allNewGroupIds = array_merge([$product->id], $variationIds);

        DB::transaction(function () use ($allOldGroupIds, $allNewGroupIds) {
            foreach ($allOldGroupIds as $pid) {
                DB::table('public.webshop_product_variations')
                    ->where(function ($q) use ($pid, $allOldGroupIds) {
                        $q->where('product_id', $pid)->whereIn('variation_product_id', $allOldGroupIds);
                    })->orWhere(function ($q) use ($pid, $allOldGroupIds) {
                        $q->whereIn('product_id', $allOldGroupIds)->where('variation_product_id', $pid);
                    })->delete();
            }

            if (count($allNewGroupIds) > 1) {
                $now = now();
                foreach ($allNewGroupIds as $pid1) {
                    foreach ($allNewGroupIds as $pid2) {
                        if ($pid1 !== $pid2) {
                            if (!DB::table('public.webshop_product_variations')
                                ->where('product_id', $pid1)
                                ->where('variation_product_id', $pid2)
                                ->exists()) {
                                DB::table('public.webshop_product_variations')->insert([
                                    'product_id' => $pid1,
                                    'variation_product_id' => $pid2,
                                    'created_at' => $now,
                                    'updated_at' => $now,
                                ]);
                            }
                        }
                    }
                }
            }
        });
    }

    public static function removeProductFromVariations(WebshopProduct $product): void
    {
        DB::table('public.webshop_product_variations')
            ->where('product_id', $product->id)
            ->orWhere('variation_product_id', $product->id)
            ->delete();
    }
}
