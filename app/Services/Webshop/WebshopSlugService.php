<?php

namespace Weboldalnet\WebshopAiDefault\Services\Webshop;

use Weboldalnet\WebshopAiDefault\Support\WebshopConnection;

class WebshopSlugService
{
    public static function generateUniqueSlug(string $name, string $table, ?int $excludeId = null): string
    {
        $slug = getTransformedString($name);
        if (empty($slug)) $slug = 'item';

        $query = WebshopConnection::db()->table($table)->where('slug', $slug)->whereNull('deleted_at');
        if ($excludeId) $query->where('id', '!=', $excludeId);
        if (!$query->exists()) return $slug;

        $counter = 1;
        do {
            $newSlug = $slug . '-' . $counter;
            $eq = WebshopConnection::db()->table($table)->where('slug', $newSlug)->whereNull('deleted_at');
            if ($excludeId) $eq->where('id', '!=', $excludeId);
            $exists = $eq->exists();
            $counter++;
        } while ($exists);

        return $newSlug;
    }

    /**
     * Nyelvi slug a lefordított névből, nyelvenként egyedi (webshop_translations részleges egyedi indexe). Az alapnyelvi
     * slugokkal nem kell egyeznie: a nyelvi cím előbb a fordítástáblában keres (WebshopTranslationService::recordIdBySlug).
     */
    public static function generateUniqueTranslatedSlug(string $name, string $type, string $locale, int $recordId): string
    {
        $slug = getTransformedString($name);
        if (empty($slug)) $slug = 'item';

        $taken = function (string $candidate) use ($type, $locale, $recordId) {
            return \Weboldalnet\WebshopAiDefault\Models\WebshopTranslation::query()
                ->where(['type' => $type, 'locale' => $locale, 'field' => 'slug', 'value' => $candidate])
                ->where('record_id', '!=', $recordId)
                ->exists();
        };

        $candidate = $slug;
        $counter = 1;
        while ($taken($candidate)) {
            $candidate = $slug . '-' . $counter++;
        }

        return $candidate;
    }
}
