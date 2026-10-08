<?php

namespace Weboldalnet\WebshopAiDefault\Services\Webshop;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Weboldalnet\WebshopAiDefault\Models\WebshopProduct;
use Weboldalnet\WebshopAiDefault\Models\WebshopProductProperty;
use Weboldalnet\WebshopAiDefault\Models\WebshopPropertyCategory;

class WebshopProductFilterService
{
    public function filter(Builder|Relation $query, array $filters): Builder|Relation
    {
        // 1. Checkbox/Radio szűrők (tulajdonságok)
        if (isset($filters['f']) && is_array($filters['f'])) {
            foreach ($filters['f'] as $propertyCategoryId => $propertyIds) {
                if (empty($propertyIds)) continue;

                $query->whereHas('productProperties', function ($q) use ($propertyCategoryId, $propertyIds) {
                    $q->where('property_category_id', $propertyCategoryId);
                    if (is_array($propertyIds)) {
                        $q->whereIn('property_id', $propertyIds);
                    } else {
                        $q->where('property_id', $propertyIds);
                    }
                });
            }
        }

        // 2. Number szűrők (intervallum)
        if (isset($filters['n']) && is_array($filters['n'])) {
            foreach ($filters['n'] as $propertyCategoryId => $range) {
                if (empty($range['min']) && empty($range['max'])) continue;

                $query->whereHas('productProperties', function ($q) use ($propertyCategoryId, $range) {
                    $q->where('property_category_id', $propertyCategoryId);
                    if (!empty($range['min'])) {
                        $q->where('number_value', '>=', (float) $range['min']);
                    }
                    if (!empty($range['max'])) {
                        $q->where('number_value', '<=', (float) $range['max']);
                    }
                });
            }
        }

        // 3. Rendezés
        $sort = $filters['sort'] ?? 'newest';
        switch ($sort) {
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'price_asc':
                $query->orderByRaw('COALESCE(sale_price, price) ASC');
                break;
            case 'price_desc':
                $query->orderByRaw('COALESCE(sale_price, price) DESC');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        return $query;
    }

    /**
     * A kategórián ténylegesen megjeleníthető szűrő-csoportok.
     *
     * Egy csoport csak akkor jelenik meg, ha van olyan értéke, ami a kategória
     * legalább egy AKTÍV termékén szerepel (a már bejelölt érték – pl. megosztott,
     * szűrt URL-ből – mindig látszik). Számmezős csoportnál akkor, ha van számadattal
     * rendelkező termék, vagy a kérésben szerepel rá szűrés.
     *
     * A szűrő-sáv ÉS a kategórialap is ezt használja: a lap ebből tudja, hogy
     * egyáltalán ki kell-e tenni a bal oldali szűrő-oszlopot.
     *
     * @return array<int, array{group: WebshopPropertyCategory, properties: Collection}>
     */
    public static function getVisibleFilterGroups($category): array
    {
        $productIds = $category->products()->active()->select('id');

        $usedPropertyIds = WebshopProductProperty::query()
            ->whereIn('product_id', $productIds)
            ->whereNotNull('property_id')
            ->distinct()
            ->pluck('property_id')
            ->all();

        $usedNumberGroupIds = WebshopProductProperty::query()
            ->whereIn('product_id', $productIds)
            ->whereNotNull('number_value')
            ->distinct()
            ->pluck('property_category_id')
            ->all();

        $visible = [];

        foreach ($category->propertyCategories()->where('filter_enabled', true)->orderBy('sort_order')->get() as $propertyCategory) {
            if ($propertyCategory->filter_type === 'number') {
                $hasData = in_array($propertyCategory->id, $usedNumberGroupIds)
                    || request("n.{$propertyCategory->id}") !== null;

                if ($hasData) {
                    $visible[] = ['group' => $propertyCategory, 'properties' => collect()];
                }

                continue;
            }

            $selectedValues = (array) request("f.{$propertyCategory->id}");

            $properties = $propertyCategory->properties()->active()->ordered()->get()
                ->filter(function ($property) use ($usedPropertyIds, $selectedValues) {
                    return in_array($property->id, $usedPropertyIds) || in_array($property->id, $selectedValues);
                });

            if ($properties->isNotEmpty()) {
                $visible[] = ['group' => $propertyCategory, 'properties' => $properties];
            }
        }

        return $visible;
    }

    /**
     * Van-e egyáltalán megjeleníthető szűrő a kategórián?
     */
    public static function hasVisibleFilters($category): bool
    {
        return count(self::getVisibleFilterGroups($category)) > 0;
    }
}
