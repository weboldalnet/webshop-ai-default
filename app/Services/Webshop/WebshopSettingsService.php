<?php

namespace Weboldalnet\WebshopAiDefault\Services\Webshop;

use Weboldalnet\WebshopAiDefault\Models\WebshopSetting;

class WebshopSettingsService
{
    private static ?array $cache = null;

    public static function get(string $key, $default = null): ?string
    {
        $settings = self::all();
        return $settings[$key] ?? $default;
    }

    /** Az eredeti (alapnyelvi) érték, a nyelvi felülírás nélkül — a fordítás forrásához */
    public static function original(string $key, $default = null): ?string
    {
        self::all();

        return self::$cache[$key] ?? $default;
    }

    public static function getBool(string $key, bool $default = false): bool
    {
        $value = self::get($key);
        if ($value === null) return $default;
        return $value === 'true' || $value === '1';
    }

    /**
     * Az összes beállítás. Vásárlói oldalon nem alapnyelven (és a rendelés nyelvén készülő levélnél, számlánál) a
     * fordítható webshop-szövegek (WebshopTranslationService::TYPES['setting'], pl. az ÁSZF-elfogadás felirata) a
     * fordítást adják, hiányában az eredetit; adminban mindig az eredeti.
     */
    public static function all(): array
    {
        if (self::$cache === null) {
            self::$cache = WebshopSetting::pluck('value', 'key')->toArray();
        }

        $locale = WebshopTranslationService::siteLocale();
        if ($locale === null) {
            return self::$cache;
        }

        $settings = self::$cache;
        foreach (array_keys(WebshopTranslationService::fields('setting')) as $key) {
            if (isset($settings[$key]) && $settings[$key] !== '') {
                $settings[$key] = WebshopTranslationService::get('setting', 0, $locale, $key) ?? $settings[$key];
            }
        }

        return $settings;
    }

    public static function save(array $settings): void
    {
        foreach ($settings as $key => $value) {
            WebshopSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
        self::$cache = null;
    }

    public static function clearCache(): void { self::$cache = null; }
}
