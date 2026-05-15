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

    public static function getBool(string $key, bool $default = false): bool
    {
        $value = self::get($key);
        if ($value === null) return $default;
        return $value === 'true' || $value === '1';
    }

    public static function all(): array
    {
        if (self::$cache === null) {
            self::$cache = WebshopSetting::pluck('value', 'key')->toArray();
        }
        return self::$cache;
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
