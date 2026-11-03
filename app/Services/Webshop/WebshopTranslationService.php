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

    /**
     * Nyelvenkénti dokumentumok (ÁSZF, adatvédelmi tájékoztató): a célnyelven saját fájl vagy link. Ezeket nem az AI
     * fordítja, hanem a fordítás-szerkesztőben tölthetők fel / adhatók meg (fájl: webshop/documents/{nyelv}/).
     */
    const DOCUMENT_FIELDS = [
        'site_checkout_tos_path' => 'file',
        'site_checkout_tos_url' => 'url',
        'site_checkout_privacy_path' => 'file',
        'site_checkout_privacy_url' => 'url',
    ];

    /** A fordítható webshop-szövegek (setting típus) felirata a fordítás-szerkesztőben */
    const SETTING_LABELS = [
        'site_checkout_tos_path' => 'ÁSZF: feltöltött fájl',
        'site_checkout_tos_url' => 'ÁSZF: külső link',
        'site_checkout_privacy_path' => 'Adatvédelmi tájékoztató: feltöltött fájl',
        'site_checkout_privacy_url' => 'Adatvédelmi tájékoztató: külső link',
        'product_extra_gallery_title' => 'Termékoldal: a második galéria címe',
        'site_checkout_tos_label' => 'Pénztár: az ÁSZF elfogadásának felirata',
        'site_checkout_privacy_label' => 'Pénztár: az adatvédelmi nyilatkozat felirata',
        'site_checkout_notice_top_content' => 'Pénztár: felső értesítő doboz',
        'site_checkout_notice_bottom_content' => 'Pénztár: alsó értesítő doboz',
        'site_home_page_h1' => 'Webshop-nyitóoldal: főcím (H1)',
        'site_home_page_meta_title' => 'Webshop-nyitóoldal: SEO-cím',
        'site_home_page_meta_description' => 'Webshop-nyitóoldal: SEO-leírás',
        'invoice_line_shipping_name' => 'Számla: a szállítási díj tételneve',
        'invoice_line_cod_name' => 'Számla: az utánvét díjának tételneve',
        'invoice_line_coupon_name' => 'Számla: a kuponkedvezmény tételneve',
    ];

    /** Kérésen belüli gyorsítótár: [type][locale][record_id][field] => value */
    private static array $cache = [];

    /** A vásárlói nyelv kézi beállítása (levélkészítés, próbák); null = automatikus (siteLocale()) */
    private static bool $siteLocaleForced = false;
    private static ?string $forcedSiteLocale = null;

    /** Az alapnyelv (a core `Lang::DEFAULT_LANG`-ja, core nélkül az app nyelve). */
    public static function defaultLocale(): string
    {
        if (class_exists(\App\Helpers\Lang::class) && defined(\App\Helpers\Lang::class . '::DEFAULT_LANG')) {
            return \App\Helpers\Lang::DEFAULT_LANG;
        }

        return (string) config('app.locale', 'hu');
    }

    /** Többnyelvű-e a projekt (a core főkapcsolója szerint; próbához a webshop.locales felülírhatja). */
    public static function isMultiLanguage(): bool
    {
        if (is_array($override = config('webshop.locales')) && $override) {
            return count($override) > 1;
        }

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

        if (is_array($override = config('webshop.locales')) && $override) {
            return array_values(array_unique(array_merge([self::defaultLocale()], $override)));
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

    /**
     * A típus fordítható mezői. A webshop-szövegekhez (setting) a dokumentumok (nyelvenkénti fájl / link) és a
     * termékinformációk bolti alapszövegei is hozzátartoznak.
     */
    public static function fields(string $type): array
    {
        $fields = self::TYPES[$type]['fields'] ?? [];

        if ($type === 'setting') {
            $fields += self::DOCUMENT_FIELDS + self::legalDefaultFields();
        }

        return $fields;
    }

    /** A termékinformációk bolti alapszövegei (Webshop beállítások › Termékinformációk), pl. legal_default_general_manufacturer */
    public static function legalDefaultFields(): array
    {
        static $fields = null;
        if ($fields === null) {
            $fields = [];
            foreach (WebshopProductLegalInfoService::PROFILES as $profile => $definition) {
                foreach ($definition['fields'] as $field => $config) {
                    if (($config['default'] ?? false) && in_array($config['type'], ['text', 'textarea'], true)) {
                        $fields[WebshopProductLegalInfoService::defaultSettingKey($profile, $field)] = 'textarea';
                    }
                }
            }
        }

        return $fields;
    }

    /** Egy webshop-szöveg felirata a fordítás-szerkesztőben */
    public static function settingLabel(string $key): string
    {
        if (isset(self::SETTING_LABELS[$key])) {
            return self::SETTING_LABELS[$key];
        }

        foreach (WebshopProductLegalInfoService::PROFILES as $profile => $definition) {
            foreach (array_keys($definition['fields']) as $field) {
                if (WebshopProductLegalInfoService::defaultSettingKey($profile, $field) === $key) {
                    return 'Termékinformáció, bolti alapérték: ' . WebshopProductLegalInfoService::fieldLabel($field);
                }
            }
        }

        return $key;
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

    /**
     * A vásárlói oldal nyelve, ha a fordításokat kell mutatni: többnyelvű projektben, a site domainjén, nem
     * alapnyelven. Adminban, parancssorban és alapnyelven null — ott a modellek az eredeti mezőt adják
     * (HasWebshopTranslations::getAttribute).
     */
    public static function siteLocale(): ?string
    {
        if (self::$siteLocaleForced) {
            return self::$forcedSiteLocale;
        }

        if (!self::isMultiLanguage() || app()->runningInConsole()) {
            return null;
        }

        if (function_exists('getSiteDomain') && request()->getHost() !== getSiteDomain()) {
            return null;
        }

        $locale = app()->getLocale();

        return ($locale !== self::defaultLocale() && in_array($locale, self::locales(), true)) ? $locale : null;
    }

    /**
     * A vásárlói nyelv kézi beállítása (pl. levél a rendelés nyelvén parancssorból); `reset: true` visszaállítja az
     * automatikus működést.
     */
    public static function useSiteLocale(?string $locale, bool $reset = false): void
    {
        self::$siteLocaleForced = !$reset;
        self::$forcedSiteLocale = ($locale === self::defaultLocale()) ? null : $locale;
    }

    /** Webshop beállítás: nem alapnyelven a fordítatlan nevű termék rejtve marad (lista, kereső, feed, termékoldal) */
    public const SETTING_HIDE_UNTRANSLATED = 'site_hide_untranslated_products';

    /** Látszik-e a termék az adott nyelven (a „fordítatlan termék rejtve” beállítás szerint) */
    public static function isVisibleInLocale($product, string $locale): bool
    {
        if ($locale === self::defaultLocale() || !self::hidesUntranslated()) {
            return true;
        }

        return self::get(WebshopProduct::TRANSLATION_TYPE, (int) $product->getKey(), $locale, 'name') !== null;
    }

    public static function hidesUntranslated(): bool
    {
        return WebshopSettingsService::getBool(self::SETTING_HIDE_UNTRANSLATED, false);
    }

    /**
     * Lekérdezés szűkítése az adott nyelven látható termékekre (a „fordítatlan termék rejtve” beállítás szerint).
     * Alapnyelven és kikapcsolt beállításnál nem szűr.
     */
    public static function restrictToVisibleProducts($query, ?string $locale = null)
    {
        $locale = $locale ?: self::siteLocale();
        if ($locale === null || $locale === self::defaultLocale() || !self::hidesUntranslated()) {
            return $query;
        }

        $table = $query->getModel()->getTable();

        return $query->whereIn($table . '.id', WebshopTranslation::query()
            ->select('record_id')
            ->where('type', WebshopProduct::TRANSLATION_TYPE)
            ->where('locale', $locale)
            ->where('field', 'name')
            ->where('value', '<>', ''));
    }

    /** Egy rekord összes fordított mezője az adott nyelven: [mező => érték] (JSON-mezők pont-útvonallal). */
    public static function recordFields(string $type, int $recordId, string $locale): array
    {
        if (!isset(self::$cache[$type][$locale][$recordId])) {
            self::preload($type, [$recordId], $locale);
        }

        return self::$cache[$type][$locale][$recordId] ?? [];
    }

    /**
     * Nyelvi slug → rekord-azonosító (a nyelvi címek feloldásához). A slug nyelvenként egyedi (részleges egyedi index).
     */
    public static function recordIdBySlug(string $type, string $locale, string $slug): ?int
    {
        $id = WebshopTranslation::query()
            ->where(['type' => $type, 'locale' => $locale, 'field' => 'slug', 'value' => $slug])
            ->value('record_id');

        return $id === null ? null : (int) $id;
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
