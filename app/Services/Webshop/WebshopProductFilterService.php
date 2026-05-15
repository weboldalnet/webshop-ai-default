<?php

namespace Weboldalnet\WebshopAiDefault\Services\Webshop;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Weboldalnet\WebshopAiDefault\Models\WebshopProduct;

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
}
