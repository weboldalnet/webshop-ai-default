<?php

namespace Weboldalnet\WebshopAiDefault\Models\Concerns;

use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopTranslationService;

/**
 * Fordítható webshop-modell: a nem alapnyelvű szövegei a `webshop_translations` táblában vannak.
 *
 * A modell `TRANSLATION_TYPE` konstansa a WebshopTranslationService::TYPES kulcsa. Az olvasás:
 * `$model->translated('name', 'en')` — fordítás, ha van, különben az eredeti mező. A rekord végleges törlésekor
 * a fordításai is törlődnek (soft delete-nél megmaradnak, hogy visszaállításkor is meglegyenek).
 *
 * Vásárlói oldalon, nem alapnyelven (WebshopTranslationService::siteLocale()) a fordítható mezők a sima
 * `$model->name` olvasásra is a fordítást adják — így a nézeteknek nem kell tudniuk a nyelvről. A lekérdezett
 * listák fordításai egy lekérdezéssel töltődnek elő (newCollection). Adminban, parancssorban és alapnyelven ez ki
 * van kapcsolva, és a mentés sosem írja vissza a fordítást: az eredeti érték az $attributes-ban marad.
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
        $original = parent::getAttribute($field);

        if ($locale === WebshopTranslationService::defaultLocale() || !WebshopTranslationService::isMultiLanguage()) {
            return $original;
        }

        return $this->translatedValue($field, $original, $locale);
    }

    /** Van-e az adott mezőnek fordítása a nyelven (az összesítőhöz és a láthatósági szabályhoz). */
    public function hasTranslation(string $field, string $locale): bool
    {
        return WebshopTranslationService::get(static::TRANSLATION_TYPE, (int) $this->getKey(), $locale, $field) !== null;
    }

    /** Az eredeti (alapnyelvi) érték, a vásárlói nyelvi felülírás nélkül. */
    public function untranslated(string $field)
    {
        return parent::getAttribute($field);
    }

    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        if (!is_string($key) || !$this->exists) {
            return $value;
        }

        $locale = WebshopTranslationService::siteLocale();
        if ($locale === null || !array_key_exists($key, WebshopTranslationService::fields(static::TRANSLATION_TYPE))) {
            return $value;
        }

        return $this->translatedValue($key, $value, $locale);
    }

    /** Vásárlói oldalon a lekérdezett lista fordításai egyben (N+1 helyett egy lekérdezés). */
    public function newCollection(array $models = [])
    {
        $locale = WebshopTranslationService::siteLocale();
        if ($locale !== null && $models) {
            WebshopTranslationService::preload(
                static::TRANSLATION_TYPE,
                array_map(fn ($model) => (int) $model->getKey(), $models),
                $locale
            );
        }

        return parent::newCollection($models);
    }

    /**
     * Egy mező fordított értéke. JSON-mezőnél (pl. legal_info) a fordítások pont-útvonalas levelek
     * (`legal_info.general.warnings`): ezeket írja rá az eredeti tömbre, a többi levél marad.
     */
    protected function translatedValue(string $field, $original, string $locale)
    {
        $kind = WebshopTranslationService::fields(static::TRANSLATION_TYPE)[$field] ?? null;
        if ($kind !== 'json') {
            return WebshopTranslationService::get(static::TRANSLATION_TYPE, (int) $this->getKey(), $locale, $field) ?? $original;
        }

        if (!is_array($original)) {
            return $original;
        }

        foreach (WebshopTranslationService::recordFields(static::TRANSLATION_TYPE, (int) $this->getKey(), $locale) as $path => $text) {
            if (str_starts_with($path, $field . '.') && $text !== '') {
                data_set($original, substr($path, strlen($field) + 1), $text);
            }
        }

        return $original;
    }
}
