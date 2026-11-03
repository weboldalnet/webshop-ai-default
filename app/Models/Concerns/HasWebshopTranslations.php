<?php

namespace Weboldalnet\WebshopAiDefault\Models\Concerns;

use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopTranslationService;

/**
 * Fordítható webshop-modell: a nem alapnyelvű szövegei a `webshop_translations` táblában vannak.
 *
 * A modell `TRANSLATION_TYPE` konstansa a WebshopTranslationService::TYPES kulcsa. Az olvasás:
 * `$model->translated('name', 'en')` — fordítás, ha van, különben az eredeti mező. A rekord végleges törlésekor
 * a fordításai is törlődnek (soft delete-nél megmaradnak, hogy visszaállításkor is meglegyenek).
 */
trait HasWebshopTranslations
{
    public static function bootHasWebshopTranslations(): void
    {
        static::deleted(function ($model) {
            if (method_exists($model, 'isForceDeleting') && !$model->isForceDeleting()) {
                return;
            }

            WebshopTranslationService::forget(static::TRANSLATION_TYPE, (int) $model->getKey());
        });
    }

    public static function translationType(): string
    {
        return static::TRANSLATION_TYPE;
    }

    /**
     * A mező az adott nyelven (alapból a kérés nyelvén). Alapnyelven, egynyelvű projektben vagy hiányzó
     * fordításnál az eredeti érték.
     */
    public function translated(string $field, ?string $locale = null)
    {
        $locale = $locale ?: app()->getLocale();

        if ($locale === WebshopTranslationService::defaultLocale() || !WebshopTranslationService::isMultiLanguage()) {
            return $this->getAttribute($field);
        }

        return WebshopTranslationService::get(static::TRANSLATION_TYPE, (int) $this->getKey(), $locale, $field)
            ?? $this->getAttribute($field);
    }

    /** Van-e az adott mezőnek fordítása a nyelven (az összesítőhöz és a láthatósági szabályhoz). */
    public function hasTranslation(string $field, string $locale): bool
    {
        return WebshopTranslationService::get(static::TRANSLATION_TYPE, (int) $this->getKey(), $locale, $field) !== null;
    }
}
