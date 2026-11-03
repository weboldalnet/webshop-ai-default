<?php

namespace Weboldalnet\WebshopAiDefault\Services\Webshop;

use Weboldalnet\WebshopAiDefault\Models\WebshopCategory;
use Weboldalnet\WebshopAiDefault\Models\WebshopCustomContent;
use Weboldalnet\WebshopAiDefault\Models\WebshopHomeBlock;
use Weboldalnet\WebshopAiDefault\Models\WebshopProduct;
use Weboldalnet\WebshopAiDefault\Models\WebshopProductDocument;
use Weboldalnet\WebshopAiDefault\Models\WebshopProductGalleryImage;
use Weboldalnet\WebshopAiDefault\Models\WebshopProductLabel;
use Weboldalnet\WebshopAiDefault\Models\WebshopProperty;
use Weboldalnet\WebshopAiDefault\Models\WebshopPropertyCategory;
use Weboldalnet\WebshopAiDefault\Models\WebshopTranslation;
use Weboldalnet\WebshopAiDefault\Support\WebshopConnection;

/**
 * A webshop többnyelvű szövegei (2026-11-03, „egy adatbázis + fordítástábla” döntés).
 *
 * A webshop minden adata az alapnyelvi adatbázisban van; a nem alapnyelvű szövegek a `webshop_translations`
 * táblában, mezőnként és nyelvenként. Ez az osztály a fordítható tartalom EGYETLEN jegyzéke (TYPES) és a
 * tárolás/olvasás egy helye — erre épül az admin nyelvi szerkesztése, a „Webshop › Fordítások” összesítő és az
 * AI-fordítás, valamint a site nyelvi megjelenítése.
 *
 * Egynyelvű projektben (vagy alapnyelven) semmit nem csinál: minden olvasás az eredeti mezőt adja.
 */
class WebshopTranslationService
{
    /**
     * Fordítható típusok: kulcs => modell, admin felirat, fordítható mezők.
     *
     * Mező-fajták: text (egysoros), textarea (többsoros, sima szöveg), html (szerkesztős), slug (URL-rész, az adott
     * nyelvű névből képzett), json (szöveges levelek pont-útvonallal, pl. legal_info.general.warnings).
     * A `setting` és a `ui` típusnak nincs modellje: a record_id 0, a mezőnév a beállítás / nyelvi kulcs.
     */
    const TYPES = [
        'product' => [
            'model' => WebshopProduct::class,
            'label' => 'Termék',
            'fields' => [
                'name' => 'text',
                'secondary_name' => 'text',
                'short_desc' => 'textarea',
                'description' => 'html',
                'slug' => 'slug',
                'og_title' => 'text',
                'og_description' => 'textarea',
                'legal_info' => 'json',
            ],
        ],
        'category' => [
            'model' => WebshopCategory::class,
            'label' => 'Kategória',
            'fields' => [
                'name_singular' => 'text',
                'name_plural' => 'text',
                'description' => 'html',
                'slug' => 'slug',
                'og_title' => 'text',
                'og_description' => 'textarea',
            ],
        ],
        'property_category' => [
            'model' => WebshopPropertyCategory::class,
            'label' => 'Tulajdonságcsoport',
            'fields' => ['name' => 'text', 'slug' => 'slug', 'suffix' => 'text'],
        ],
        'property' => [
            'model' => WebshopProperty::class,
            'label' => 'Tulajdonság',
            'fields' => ['name' => 'text', 'slug' => 'slug'],
        ],
        'label' => [
            'model' => WebshopProductLabel::class,
            'label' => 'Címke',
            'fields' => ['name' => 'text'],
        ],
        'gallery_image' => [
            'model' => WebshopProductGalleryImage::class,
            'label' => 'Galériakép',
            'fields' => ['alt' => 'text'],
        ],
        'document' => [
            'model' => WebshopProductDocument::class,
            'label' => 'Termékdokumentum',
            'fields' => ['name' => 'text'],
        ],
        'custom_content' => [
            'model' => WebshopCustomContent::class,
            'label' => 'Levél, köszönőoldal',
            'fields' => ['title' => 'text', 'content' => 'html'],
        ],
        'home_block' => [
            'model' => WebshopHomeBlock::class,
            'label' => 'Webshop-nyitóoldal blokk',
            'fields' => ['title' => 'text', 'img_alt' => 'text', 'settings' => 'json', 'buttons' => 'json'],
        ],
        'setting' => [
            'model' => null,
            'label' => 'Webshop-szöveg',
            'fields' => [
                'product_extra_gallery_title' => 'text',
                'site_checkout_tos_label' => 'text',
                'site_checkout_privacy_label' => 'text',
                'site_checkout_notice_top_content' => 'html',
                'site_checkout_notice_bottom_content' => 'html',
                'site_home_page_h1' => 'text',
                'site_home_page_meta_title' => 'text',
                'site_home_page_meta_description' => 'textarea',
                'invoice_line_shipping_name' => 'text',
                'invoice_line_cod_name' => 'text',
                'invoice_line_coupon_name' => 'text',
            ],
        ],
        'ui' => [
            'model' => null,
            'label' => 'Felületi szöveg',
            'fields' => [],
        ],
    ];

    /** Kérésen belüli gyorsítótár: [type][locale][record_id][field] => value */
    private static array $cache = [];

    /** Az alapnyelv (a core `Lang::DEFAULT_LANG`-ja, core nélkül az app nyelve). */
    public static function defaultLocale(): string
    {
        if (class_exists(\App\Helpers\Lang::class) && defined(\App\Helpers\Lang::class . '::DEFAULT_LANG')) {
            return \App\Helpers\Lang::DEFAULT_LANG;
        }

        return (string) config('app.locale', 'hu');
    }

    /** Többnyelvű-e a projekt (a core főkapcsolója szerint). */
    public static function isMultiLanguage(): bool
    {
        return class_exists(\App\Helpers\Lang::class)
            && method_exists(\App\Helpers\Lang::class, 'isMultiLanguage')
            && \App\Helpers\Lang::isMultiLanguage();
    }

    /** Az összes nyelv (alapnyelvvel együtt). */
    public static function locales(): array
    {
        if (!self::isMultiLanguage()) {
            return [self::defaultLocale()];
        }

        return method_exists(\App\Helpers\Lang::class, 'siteLocales')
            ? array_values(\App\Helpers\Lang::siteLocales())
            : array_values(\App\Helpers\Lang::getLanguages());
    }

    /** A fordítandó (nem alap) nyelvek. */
    public static function targetLocales(): array
    {
        return array_values(array_diff(self::locales(), [self::defaultLocale()]));
    }

    public static function fields(string $type): array
    {
        return self::TYPES[$type]['fields'] ?? [];
    }

    /**
     * A fordításkor eltárolt forrás-hash: ha az eredeti szöveg azóta változott, a fordítás elavult.
     * Szóköz-különbség nem számít változásnak (mint a core fordítójánál).
     */
    public static function hash(?string $text): string
    {
        return sha1(trim((string) preg_replace('/\s+/u', ' ', (string) $text)));
    }

    /**
     * Egy mező fordítása, vagy null, ha nincs (üres) fordítás.
     */
    public static function get(string $type, int $recordId, string $locale, string $field): ?string
    {
        if (!isset(self::$cache[$type][$locale][$recordId])) {
            self::preload($type, [$recordId], $locale);
        }

        $value = self::$cache[$type][$locale][$recordId][$field] ?? null;

        return $value === null || $value === '' ? null : $value;
    }

    /**
     * Több rekord fordításainak előtöltése egy lekérdezéssel (listákhoz, N+1 ellen).
     */
    public static function preload(string $type, array $recordIds, string $locale): void
    {
        $missing = array_values(array_filter(array_unique(array_map('intval', $recordIds)), function ($id) use ($type, $locale) {
            return !isset(self::$cache[$type][$locale][$id]);
        }));

        if (!$missing) {
            return;
        }

        foreach ($missing as $id) {
            self::$cache[$type][$locale][$id] = [];
        }

        WebshopTranslation::query()
            ->where('type', $type)
            ->where('locale', $locale)
            ->whereIn('record_id', $missing)
            ->get(['record_id', 'field', 'value'])
            ->each(function (WebshopTranslation $row) use ($type, $locale) {
                self::$cache[$type][$locale][$row->record_id][$row->field] = $row->value;
            });
    }

    /**
     * Fordítás mentése (üres értéknél törlés). A forrás-hash az eredeti szövegből készül, így később kiderül,
     * ha az eredeti megváltozott.
     */
    public static function set(
        string $type,
        int $recordId,
        string $locale,
        string $field,
        ?string $value,
        ?string $sourceText,
        string $origin = WebshopTranslation::ORIGIN_MANUAL,
        ?int $adminId = null
    ): void {
        unset(self::$cache[$type][$locale][$recordId]);

        if ($value === null || trim($value) === '') {
            WebshopTranslation::query()
                ->where(['type' => $type, 'record_id' => $recordId, 'locale' => $locale, 'field' => $field])
                ->delete();

            return;
        }

        WebshopTranslation::query()->updateOrCreate(
            ['type' => $type, 'record_id' => $recordId, 'locale' => $locale, 'field' => $field],
            ['value' => $value, 'source_hash' => self::hash($sourceText), 'origin' => $origin, 'admin_id' => $adminId]
        );
    }

    /** Egy rekord összes fordításának törlése (a rekord végleges törlésekor). */
    public static function forget(string $type, int $recordId): void
    {
        unset(self::$cache[$type]);

        WebshopTranslation::query()->where('type', $type)->where('record_id', $recordId)->delete();
    }

    /** Tesztekhez és hosszú futású folyamatokhoz. */
    public static function flushCache(): void
    {
        self::$cache = [];
    }

    /** A közös kapcsolat (tranzakcióhoz a tömeges műveleteknél). */
    public static function db()
    {
        return WebshopConnection::db();
    }
}
