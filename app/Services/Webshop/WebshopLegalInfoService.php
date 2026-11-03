<?php

namespace Weboldalnet\WebshopAiDefault\Services\Webshop;

use App\Models\ContactSettings;

/**
 * A webshop jogi tájékoztatásához szükséges adatok egy helyen.
 *
 * Az eladó adatai (e-kereskedelmi törvény: név, székhely, nyilvántartási szám,
 * adószám, elérhetőség), az ÁSZF és az adatvédelmi tájékoztató helye. A rendelés-
 * visszaigazoló levél, az elállási nyilatkozatminta és az admin figyelmeztetései
 * mind innen olvasnak, hogy minden webshopban – termékkörtől függetlenül –
 * ugyanaz a forrás legyen.
 *
 * Ahol a webshop saját beállítása üres, a core kapcsolati adataira és
 * dokumentumaira esünk vissza, így egy már beállított oldalon nem kell semmit
 * kétszer megadni.
 */
class WebshopLegalInfoService
{
    /** Az eladó adatainak beállítás-kulcsai (admin: Webshop → Dokumentumok). */
    public const SELLER_FIELDS = [
        'name' => 'Eladó neve (cégnév / egyéni vállalkozó neve)',
        'seat' => 'Székhely',
        'registration_number' => 'Cégjegyzékszám / nyilvántartási szám',
        'tax_number' => 'Adószám',
        'email' => 'E-mail-cím',
        'phone' => 'Telefonszám',
    ];

    /** A jogszabály szerint mindig elérhetővé teendő adatok – ezek hiányát az admin jelzi. */
    public const REQUIRED_SELLER_FIELDS = ['name', 'seat', 'registration_number', 'tax_number', 'email'];

    public static function settingKey(string $field): string
    {
        return 'legal_seller_' . $field;
    }

    /**
     * A SELLER_FIELDS megnevezései az aktuális nyelven (a konstans a magyar forrás).
     *
     * @return array<string, string>
     */
    public static function sellerFieldLabels(): array
    {
        $labels = [];

        foreach (array_keys(self::SELLER_FIELDS) as $field) {
            $labels[$field] = __('webshop::legal_info.seller.' . $field);
        }

        return $labels;
    }

    /**
     * Az eladó adatai, a core kapcsolati adataira visszaeséssel.
     *
     * @return array<string, string|null>
     */
    public static function seller(): array
    {
        $contact = self::contactData();
        $seller = [];

        foreach (array_keys(self::SELLER_FIELDS) as $field) {
            $value = trim((string) WebshopSettingsService::get(self::settingKey($field), ''));
            $seller[$field] = $value !== '' ? $value : null;
        }

        $seller['name'] ??= config('app.shop_name') ?: null;
        $seller['seat'] ??= ($contact['address'] ?? null) ?: null;
        $seller['email'] ??= ($contact['email'] ?? null) ?: (config('app.shop_email') ?: null);
        $seller['phone'] ??= ($contact['phone'] ?? null) ?: null;

        return $seller;
    }

    /**
     * Hiányzó kötelező eladói adatok (a megnevezésükkel) – az admin figyelmeztetéshez.
     *
     * @return string[]
     */
    public static function missingSellerFields(): array
    {
        $seller = self::seller();
        $labels = self::sellerFieldLabels();
        $missing = [];

        foreach (self::REQUIRED_SELLER_FIELDS as $field) {
            if (empty($seller[$field])) {
                $missing[] = $labels[$field];
            }
        }

        return $missing;
    }

    /**
     * Az ÁSZF abszolút URL-je: külső link, vagy a feltöltött fájl.
     */
    public static function termsUrl(): ?string
    {
        $value = self::documentSetting(['site_checkout_tos_url', 'site_checkout_tos_path']);

        return $value !== null ? self::absolute($value) : null;
    }

    /**
     * Egy dokumentum (link vagy feltöltött fájl) a kérés / a rendelés nyelvén: nem alapnyelven előbb a nyelvi
     * változat (Fordítások › Webshop-szövegek), bármelyik formában — csak ha nincs, akkor az alapnyelvi, a
     * megadott sorrendben (link, fájl).
     */
    private static function documentSetting(array $keys): ?string
    {
        $locale = WebshopTranslationService::siteLocale();
        if ($locale !== null) {
            foreach ($keys as $key) {
                $value = trim((string) WebshopTranslationService::get('setting', 0, $locale, $key));
                if ($value !== '') {
                    return $value;
                }
            }
        }

        foreach ($keys as $key) {
            $value = trim((string) WebshopSettingsService::original($key, ''));
            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }

    /**
     * A feltöltött ÁSZF helyi elérési útja (a levél mellékletéhez), ha PDF és létezik.
     */
    public static function termsLocalPdf(): ?string
    {
        $path = trim((string) WebshopSettingsService::original('site_checkout_tos_path', ''));

        // a rendelés nyelvén (a levél WebshopOrderLocale::run alatt készül): a nyelvi fájl; ha a nyelvi ÁSZF csak
        // linkként van megadva, a más nyelvű fájlt nem csatoljuk
        $locale = WebshopTranslationService::siteLocale();
        if ($locale !== null) {
            $localPath = trim((string) WebshopTranslationService::get('setting', 0, $locale, 'site_checkout_tos_path'));
            $localUrl = trim((string) WebshopTranslationService::get('setting', 0, $locale, 'site_checkout_tos_url'));
            if ($localPath !== '') {
                $path = $localPath;
            } elseif ($localUrl !== '') {
                return null;
            }
        }

        if ($path === '' || !str_starts_with($path, '/storage/') || strtolower(pathinfo($path, PATHINFO_EXTENSION)) !== 'pdf') {
            return null;
        }

        $local = storage_path('app/public/' . substr($path, strlen('/storage/')));

        // 10 MB felett nem csatoljuk: a levelezők egy része eldobná a levelet.
        return is_file($local) && filesize($local) <= 10 * 1024 * 1024 ? $local : null;
    }

    /**
     * Az adatvédelmi tájékoztató abszolút URL-je: a webshop beállítása, vagy a core
     * „Dokumentumok” közül az adatvédelmi tájékoztató / irányelvek.
     */
    public static function privacyUrl(): ?string
    {
        $value = self::documentSetting(['site_checkout_privacy_url', 'site_checkout_privacy_path']);
        if ($value !== null) {
            return self::absolute($value);
        }

        $docs = self::coreDocuments();

        foreach (['GTC', 'PRIVACY'] as $constant) {
            $id = defined('\App\Helpers\DocumentHelper::' . $constant) ? constant('\App\Helpers\DocumentHelper::' . $constant) : null;
            if ($id !== null && !empty($docs[$id])) {
                return self::absolute($docs[$id]);
            }
        }

        return null;
    }

    /**
     * Csatolja-e a rendelés-visszaigazoló levél az ÁSZF-et (alapból igen: a link
     * önmagában nem tartós adathordozó).
     */
    public static function attachTermsToOrderMail(): bool
    {
        return WebshopSettingsService::getBool('site_order_mail_attach_tos', true);
    }

    private static function absolute(string $urlOrPath): string
    {
        return preg_match('#^https?://#i', $urlOrPath) ? $urlOrPath : url($urlOrPath);
    }

    private static function contactData(): array
    {
        try {
            $data = optional(ContactSettings::find(1))->contact_data;
        } catch (\Throwable $e) {
            return [];
        }

        return is_array($data) ? $data : [];
    }

    private static function coreDocuments(): array
    {
        try {
            $docs = optional(ContactSettings::find(1))->docs;
        } catch (\Throwable $e) {
            return [];
        }

        return is_array($docs) ? $docs : [];
    }
}
