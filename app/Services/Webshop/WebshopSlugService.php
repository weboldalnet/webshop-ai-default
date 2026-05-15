<?php

namespace Weboldalnet\WebshopAiDefault\Services\Webshop;

use Illuminate\Support\Facades\DB;

class WebshopSlugService
{
    public static function generateUniqueSlug(string $name, string $table, ?int $excludeId = null): string
    {
        $slug = getTransformedString($name);
        if (empty($slug)) $slug = 'item';

        $query = DB::table($table)->where('slug', $slug)->whereNull('deleted_at');
        if ($excludeId) $query->where('id', '!=', $excludeId);
        if (!$query->exists()) return $slug;

        $counter = 1;
        do {
            $newSlug = $slug . '-' . $counter;
            $eq = DB::table($table)->where('slug', $newSlug)->whereNull('deleted_at');
            if ($excludeId) $eq->where('id', '!=', $excludeId);
            $exists = $eq->exists();
            $counter++;
        } while ($exists);

        return $newSlug;
    }
}
