<?php

namespace Weboldalnet\WebshopAiDefault\Services\Webshop;

use Illuminate\Support\HtmlString;
use Weboldalnet\WebshopAiDefault\Models\WebshopProduct;

/**
 * Kötelező termékinformációk online értékesítésnél, termékkörönként („profilok”).
 *
 * Távollévők közötti értékesítésnél bizonyos adatoknak a vásárlás ELŐTT, a termékoldalon
 * kell látszaniuk. Hogy melyek, az a termék fajtájától függ, ezért a bolt az adminban
 * kapcsolja be a rá vonatkozó profilokat (Webshop → Extra beállítások → Termékinformációk):
 *
 *  - general:  általános termékbiztonság, 2023/988/EU (GPSR) 19. cikk – gyártó, uniós
 *              felelős személy, termékazonosító, figyelmeztetések (nem élelmiszerekre);
 *  - food:     élelmiszer, 1169/2011/EU 14. cikk – összetevők, allergének, tápérték,
 *              tárolás, eredet, élelmiszer-vállalkozó;
 *  - chemical: veszélyes keverék, 1272/2008/EK (CLP) 48. cikk – piktogramok,
 *              figyelmeztetés, H- és P-mondatok, UFI;
 *  - energy:   energiacímkés termék, 2017/1369/EU – energiaosztály, címke, adatlap;
 *  - textile:  textiltermék, 1007/2011/EU – szálösszetétel, kezelési útmutató.
 *
 * Az adatok a termék legal_info JSON-oszlopában vannak ({profil: {mező: érték}}); a
 * „default” jelölésű mezőkhöz bolt szintű alapérték adható (pl. ha a bolt maga a gyártó),
 * ami üres termékmezőnél érvényes. A termékoldal csak a kitöltött mezőket mutatja.
 */
class WebshopProductLegalInfoService
{
    public const PROFILES_KEY = 'legal_product_profiles';

    public const PROFILES = [
        'general' => [
            'label' => 'Általános termékbiztonság (GPSR)',
            'description' => 'Nem élelmiszer jellegű fogyasztói termékekhez (2023/988/EU rendelet).',
            'fields' => [
                'manufacturer' => ['label' => 'Gyártó', 'type' => 'textarea', 'default' => true,
                    'hint' => 'Név (vagy bejegyzett márkanév), postai cím és elektronikus elérhetőség (e-mail vagy weboldal).'],
                'eu_responsible' => ['label' => 'Uniós felelős személy', 'type' => 'textarea', 'default' => true,
                    'hint' => 'Csak ha a gyártó nem az EU-ban van letelepedve: a felelős gazdasági szereplő neve, postai és elektronikus címe.'],
                'identifier' => ['label' => 'Termékazonosító', 'type' => 'text',
                    'hint' => 'Típus, modell, tétel- vagy sorozatszám – ami alapján a termék azonosítható.'],
                'warnings' => ['label' => 'Figyelmeztetések, biztonsági információk', 'type' => 'textarea',
                    'hint' => 'A terméken/csomagoláson szereplő figyelmeztetések magyarul (pl. „3 éven aluli gyermekek számára nem alkalmas”).'],
            ],
        ],
        'food' => [
            'label' => 'Élelmiszer',
            'description' => 'Élelmiszerekhez, italokhoz, étrend-kiegészítőkhöz (1169/2011/EU rendelet).',
            'fields' => [
                'ingredients' => ['label' => 'Összetevők', 'type' => 'textarea',
                    'hint' => 'Az allergéneket emeld ki dupla csillaggal, pl. **búzaliszt**, **tej** – a termékoldalon félkövérrel jelennek meg.'],
                'allergens' => ['label' => 'Allergének', 'type' => 'text',
                    'hint' => 'Pl. „Glutént, tejet tartalmaz. Nyomokban diót tartalmazhat.”'],
                'nutrition' => ['label' => 'Tápérték 100 g / 100 ml termékben', 'type' => 'nutrition'],
                'storage' => ['label' => 'Tárolási és felhasználási feltételek', 'type' => 'textarea'],
                'origin' => ['label' => 'Származási ország / eredet helye', 'type' => 'text'],
                'alcohol' => ['label' => 'Alkoholtartalom', 'type' => 'text',
                    'hint' => '1,2 térfogatszázalék felett kötelező, pl. „12,5% vol”.'],
                'operator' => ['label' => 'Élelmiszer-vállalkozó', 'type' => 'textarea', 'default' => true,
                    'hint' => 'Annak a neve és címe, akinek a nevén a termék forgalomba kerül.'],
            ],
        ],
        'chemical' => [
            'label' => 'Veszélyes keverék (CLP)',
            'description' => 'Tisztítószerekhez, festékekhez, ragasztókhoz és más veszélyesként címkézett keverékekhez (1272/2008/EK rendelet).',
            'fields' => [
                'signal_word' => ['label' => 'Figyelmeztetés', 'type' => 'select', 'options' => ['' => '–', 'Figyelem' => 'Figyelem', 'Veszély' => 'Veszély']],
                'pictograms' => ['label' => 'Veszélyt jelző piktogramok', 'type' => 'pictograms'],
                'hazard_statements' => ['label' => 'Figyelmeztető mondatok (H)', 'type' => 'textarea',
                    'hint' => 'A címkén szereplő H-mondatok, pl. „H319 Súlyos szemirritációt okoz.”'],
                'precautionary_statements' => ['label' => 'Óvintézkedésre vonatkozó mondatok (P)', 'type' => 'textarea'],
                'ufi' => ['label' => 'UFI-kód', 'type' => 'text'],
            ],
        ],
        'energy' => [
            'label' => 'Energiacímke',
            'description' => 'Energiacímkés termékekhez: háztartási gépek, világítás, TV, kijelzők stb. (2017/1369/EU rendelet).',
            'fields' => [
                'energy_class' => ['label' => 'Energiahatékonysági osztály', 'type' => 'select',
                    'options' => ['' => '–', 'A' => 'A', 'B' => 'B', 'C' => 'C', 'D' => 'D', 'E' => 'E', 'F' => 'F', 'G' => 'G']],
                'label_url' => ['label' => 'Energiacímke (kép vagy PDF linkje)', 'type' => 'url'],
                'sheet_url' => ['label' => 'Termékinformációs adatlap linkje', 'type' => 'url',
                    'hint' => 'Pl. az EPREL-adatbázis termékoldala.'],
            ],
        ],
        'textile' => [
            'label' => 'Textiltermék',
            'description' => 'Ruházathoz, lakástextilhez (1007/2011/EU rendelet).',
            'fields' => [
                'fibre_composition' => ['label' => 'Szálösszetétel', 'type' => 'text',
                    'hint' => 'Pl. „95% pamut, 5% elasztán”.'],
                'care' => ['label' => 'Kezelési útmutató', 'type' => 'textarea'],
            ],
        ],
    ];

    public const PICTOGRAMS = [
        'GHS01' => 'Robbanóanyag',
        'GHS02' => 'Tűzveszélyes',
        'GHS03' => 'Oxidáló',
        'GHS04' => 'Nyomás alatti gáz',
        'GHS05' => 'Maró hatású',
        'GHS06' => 'Akut toxicitás',
        'GHS07' => 'Ártalmas, irritáló',
        'GHS08' => 'Egészségkárosító',
        'GHS09' => 'Környezetre veszélyes',
    ];

    public const NUTRITION = [
        'energy' => 'Energia (kJ / kcal)',
        'fat' => 'Zsír',
        'saturates' => '– amelyből telített zsírsavak',
        'carbohydrate' => 'Szénhidrát',
        'sugars' => '– amelyből cukrok',
        'fibre' => 'Rost',
        'protein' => 'Fehérje',
        'salt' => 'Só',
    ];

    /**
     * A boltban bekapcsolt profilok (alapértelmezés: csak az általános).
     */
    public static function enabledProfiles(): array
    {
        $value = WebshopSettingsService::get(self::PROFILES_KEY, 'general');
        $keys = array_filter(array_map('trim', explode(',', (string) $value)));

        return array_values(array_intersect(array_keys(self::PROFILES), $keys));
    }

    public static function defaultSettingKey(string $profile, string $field): string
    {
        return 'legal_default_' . $profile . '_' . $field;
    }

    /**
     * Bolt szintű alapértékek a „default” jelölésű mezőkhöz.
     */
    public static function shopDefaults(): array
    {
        $defaults = [];

        foreach (self::PROFILES as $profile => $definition) {
            foreach ($definition['fields'] as $field => $config) {
                if ($config['default'] ?? false) {
                    $value = trim((string) WebshopSettingsService::get(self::defaultSettingKey($profile, $field), ''));
                    if ($value !== '') {
                        $defaults[$profile][$field] = $value;
                    }
                }
            }
        }

        return $defaults;
    }

    /**
     * Admin bemenet szűrése mentéshez: csak ismert profil/mező, trimelt szöveg, ismert piktogram.
     */
    public static function sanitize(?array $input): ?array
    {
        $clean = [];

        foreach (self::PROFILES as $profile => $definition) {
            foreach ($definition['fields'] as $field => $config) {
                $value = $input[$profile][$field] ?? null;

                if ($config['type'] === 'pictograms') {
                    $value = array_values(array_intersect(array_keys(self::PICTOGRAMS), (array) $value));
                    if ($value) {
                        $clean[$profile][$field] = $value;
                    }
                    continue;
                }

                if ($config['type'] === 'nutrition') {
                    $rows = [];
                    foreach (array_keys(self::NUTRITION) as $key) {
                        $row = trim((string) ($value[$key] ?? ''));
                        if ($row !== '') {
                            $rows[$key] = mb_substr($row, 0, 100);
                        }
                    }
                    if ($rows) {
                        $clean[$profile][$field] = $rows;
                    }
                    continue;
                }

                $value = trim((string) $value);

                if ($config['type'] === 'select' && !array_key_exists($value, $config['options'] ?? [])) {
                    $value = '';
                }
                if ($config['type'] === 'url' && $value !== '' && !preg_match('#^(https?://|/)#i', $value)) {
                    $value = '';
                }

                if ($value !== '') {
                    $clean[$profile][$field] = mb_substr($value, 0, 5000);
                }
            }
        }

        return $clean ?: null;
    }

    /**
     * A termékoldalon megjelenítendő szakaszok: [profil => [label, rows => [[label, type, value]]]].
     * Csak a bekapcsolt profilok kitöltött (vagy bolt szinten alapértékkel bíró) mezői.
     */
    public static function sectionsFor(WebshopProduct $product): array
    {
        $data = is_array($product->legal_info ?? null) ? $product->legal_info : [];
        $defaults = self::shopDefaults();
        $sections = [];

        foreach (self::enabledProfiles() as $profile) {
            $rows = [];

            foreach (self::PROFILES[$profile]['fields'] as $field => $config) {
                $value = $data[$profile][$field] ?? null;

                if (($value === null || $value === '' || $value === []) && ($config['default'] ?? false)) {
                    $value = $defaults[$profile][$field] ?? null;
                }

                if ($value === null || $value === '' || $value === []) {
                    continue;
                }

                $rows[] = ['label' => $config['label'], 'type' => $config['type'], 'value' => $value];
            }

            // Élelmiszernél a nettó mennyiség is kötelező – a kiszerelésből (egységár-mezők) jön
            if ($profile === 'food') {
                $quantity = WebshopUnitPriceService::quantityLabel($product->unit_quantity ?? null, $product->unit_measure ?? null);
                if ($quantity && ($product->unit_measure ?? null) !== 'pcs') {
                    array_unshift($rows, ['label' => 'Nettó mennyiség', 'type' => 'text', 'value' => $quantity]);
                }
            }

            if ($rows) {
                $sections[$profile] = ['label' => self::PROFILES[$profile]['label'], 'rows' => $rows];
            }
        }

        return $sections;
    }

    public static function hasInfo(WebshopProduct $product): bool
    {
        return !empty(self::sectionsFor($product));
    }

    /**
     * Szöveg biztonságos kiírása: escape, **kiemelés** → <strong>, sortörés → <br>.
     */
    public static function formatText(string $value): HtmlString
    {
        $html = e($value);
        $html = preg_replace('/\*\*(.+?)\*\*/su', '<strong>$1</strong>', $html);

        return new HtmlString(nl2br($html));
    }
}
