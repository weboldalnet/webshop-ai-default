<?php

namespace Weboldalnet\WebshopAiDefault\Services\Webshop;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Weboldalnet\WebshopAiDefault\Helpers\WebshopUrl;
use Weboldalnet\WebshopAiDefault\Models\WebshopTranslation;

/**
 * „Webshop › Fordítások” összesítő: tételek típusonként, nyelvenkénti állapottal, szűrőkkel; nyelvenkénti
 * összesítő kártyák. Az állapot a forrás-hash miatt PHP-ban számolódik (a kiválasztott típus összes, a szűrőnek
 * megfelelő rekordjára — egy webshop méreténél ez gyors; a kártyák 60 mp-ig gyorsítótárban).
 */
class WebshopTranslationOverviewService
{
    public const PER_PAGE = 25;

    public const STATES = [
        'missing' => 'Hiányzik',
        'partial' => 'Részben kész',
        'outdated' => 'Elavult',
        'done' => 'Kész',
        'manual' => 'Kézi fordítás',
    ];

    /** Típusonként: keresett oszlopok, van-e is_active, előtöltött kapcsolatok, rendezés */
    private const TYPE_CONFIG = [
        'product' => ['search' => ['name', 'secondary_name', 'sku'], 'active' => true, 'with' => ['category'], 'order' => 'name'],
        'category' => ['search' => ['name_singular', 'name_plural'], 'active' => true, 'with' => [], 'order' => 'name_singular'],
        'property_category' => ['search' => ['name'], 'active' => true, 'with' => [], 'order' => 'name'],
        'property' => ['search' => ['name'], 'active' => true, 'with' => ['propertyCategory'], 'order' => 'name'],
        'label' => ['search' => ['name'], 'active' => false, 'with' => [], 'order' => 'name'],
        'gallery_image' => ['search' => ['alt'], 'active' => true, 'with' => ['product'], 'order' => 'product_id'],
        'document' => ['search' => ['name'], 'active' => true, 'with' => ['product'], 'order' => 'product_id'],
        'custom_content' => ['search' => ['title'], 'active' => true, 'with' => [], 'order' => 'id'],
        'home_block' => ['search' => ['title'], 'active' => true, 'with' => [], 'order' => 'sort_order'],
    ];

    /** @return array<string, string> típus => felirat */
    public static function types(): array
    {
        $types = [];
        foreach (WebshopTranslationRunService::RUN_TYPES as $type) {
            $types[$type] = WebshopTranslationService::TYPES[$type]['label'] ?? $type;
        }

        return $types;
    }

    public static function normalizeFilters(array $input): array
    {
        $types = self::types();
        $locales = WebshopTranslationService::targetLocales();

        return [
            'type' => array_key_exists($input['type'] ?? '', $types) ? $input['type'] : 'product',
            'locale' => in_array($input['locale'] ?? '', $locales, true) ? $input['locale'] : ($locales[0] ?? null),
            'state' => array_key_exists($input['state'] ?? '', self::STATES) ? $input['state'] : '',
            'category_id' => (int) ($input['category_id'] ?? 0) ?: null,
            'active' => in_array($input['active'] ?? '', ['1', '0'], true) ? $input['active'] : '',
            'q' => trim((string) ($input['q'] ?? '')),
            'page' => max(1, (int) ($input['page'] ?? 1)),
        ];
    }

    /** A típus szűrőnek megfelelő rekordjai (állapot-szűrés nélkül) */
    public static function records(string $type, array $filters)
    {
        if ($type === 'setting') {
            return collect([WebshopTranslationStatusService::settingRecord()]);
        }

        $class = WebshopTranslationRunService::modelClass($type);
        $config = self::TYPE_CONFIG[$type];
        $query = $class::query()->with($config['with']);

        if ($filters['q'] !== '') {
            $pattern = '%' . addcslashes($filters['q'], '%_' . chr(92)) . '%';
            $query->where(function ($q) use ($config, $pattern, $filters) {
                foreach ($config['search'] as $column) {
                    $q->orWhere($column, 'ilike', $pattern);
                }
                if (ctype_digit($filters['q'])) {
                    $q->orWhere('id', (int) $filters['q']);
                }
            });
        }
        if ($config['active'] && $filters['active'] !== '') {
            $query->where('is_active', $filters['active'] === '1');
        }
        if ($type === 'product' && $filters['category_id']) {
            $query->where('category_id', $filters['category_id']);
        }

        return $query->orderBy($config['order'])->orderBy('id')->get();
    }

    /**
     * Lista-sorok: rekord, felirat, alcím, kép, nyelvenkénti összegzés, utolsó fordítás, linkek. A fordítanivaló
     * nélküli rekordok (pl. alt-szöveg nélküli galériakép) kimaradnak.
     */
    public static function rows(string $type, $records, array $locales): array
    {
        $ids = $records->map(fn ($record) => (int) $record->getKey())->all();
        $translations = WebshopTranslationStatusService::translationRows($type, $ids, $locales);

        $rows = [];
        foreach ($records as $record) {
            $id = (int) $record->getKey();
            $sources = WebshopTranslationStatusService::sourceFields($type, $record);
            if (!$sources) {
                continue;
            }

            $perLocale = [];
            $last = null;
            foreach ($locales as $locale) {
                $states = [];
                foreach ($sources as $field => $source) {
                    $row = $translations[$id][$locale][$field] ?? null;
                    $states[$field] = [
                        'state' => WebshopTranslationStatusService::stateOf($source, $row),
                        'origin' => $row['origin'] ?? null,
                    ];
                    if ($row && (!$last || $row['updated_at'] > $last['at'])) {
                        $last = ['at' => $row['updated_at'], 'origin' => $row['origin'], 'locale' => $locale];
                    }
                }
                $summary = WebshopTranslationStatusService::summarize($states);
                $summary['overall'] = WebshopTranslationStatusService::overallState($summary);
                $perLocale[$locale] = $summary;
            }

            $rows[] = [
                'id' => $id,
                'type' => $type,
                'label' => WebshopTranslationRunService::recordLabel($type, $record),
                'sub' => self::subtitle($type, $record),
                'thumb' => self::thumb($type, $record),
                'active' => !($record instanceof \Illuminate\Database\Eloquent\Model) || !array_key_exists('is_active', $record->getAttributes()) || (bool) $record->is_active,
                'locales' => $perLocale,
                'last' => $last,
                'edit_url' => self::adminUrl($type, $record),
                'site_urls' => self::siteUrls($type, $record, $locales),
            ];
        }

        return $rows;
    }

    public static function filterByState(array $rows, ?string $locale, string $state): array
    {
        if ($state === '' || $locale === null) {
            return $rows;
        }

        return array_values(array_filter($rows, function ($row) use ($locale, $state) {
            $summary = $row['locales'][$locale] ?? null;
            if (!$summary) {
                return false;
            }

            return $state === 'manual' ? $summary['manual'] > 0 : $summary['overall'] === $state;
        }));
    }

    public static function paginate(array $rows, int $page, string $path, array $query): LengthAwarePaginator
    {
        return (new LengthAwarePaginator(
            array_slice($rows, ($page - 1) * self::PER_PAGE, self::PER_PAGE),
            count($rows),
            self::PER_PAGE,
            $page,
            ['path' => $path]
        ))->appends(array_filter($query, fn ($value) => $value !== '' && $value !== null));
    }

    /**
     * Nyelvenkénti összesítő az összes típusra: [nyelv => ['total', 'done', 'missing', 'outdated', 'manual', 'percent']]
     */
    public static function kpis(array $locales): array
    {
        $key = 'webshop_translation_kpis_' . implode('_', $locales) . '_'
            . md5((string) WebshopTranslation::query()->max('updated_at') . '|' . WebshopTranslation::query()->count());

        return Cache::remember($key, 60, function () use ($locales) {
            $totals = array_fill_keys($locales, ['total' => 0, 'done' => 0, 'missing' => 0, 'outdated' => 0, 'manual' => 0]);
            $empty = ['type' => '', 'locale' => null, 'state' => '', 'category_id' => null, 'active' => '', 'q' => '', 'page' => 1];

            foreach (array_keys(self::types()) as $type) {
                foreach (self::rows($type, self::records($type, $empty), $locales) as $row) {
                    foreach ($row['locales'] as $locale => $summary) {
                        foreach (['total', 'done', 'missing', 'outdated', 'manual'] as $metric) {
                            $totals[$locale][$metric] += $summary[$metric];
                        }
                    }
                }
            }

            foreach ($totals as $locale => $summary) {
                $totals[$locale]['percent'] = $summary['total'] ? (int) floor($summary['done'] * 100 / $summary['total']) : 100;
            }

            return $totals;
        });
    }

    protected static function subtitle(string $type, $record): ?string
    {
        switch ($type) {
            case 'product':
                return trim(($record->sku ? $record->sku . ' · ' : '') . ($record->category?->untranslated('name_singular') ?? ''), ' ·') ?: null;
            case 'property':
                return $record->propertyCategory?->untranslated('name');
            case 'gallery_image':
            case 'document':
                return $record->product?->untranslated('name');
            case 'custom_content':
                return trim(implode(' · ', array_filter([$record->type, $record->checkout_mode, $record->payment_method, $record->shipping_method])));
            case 'home_block':
                return $record->type;
            case 'setting':
                return 'ÁSZF és adatvédelem (felirat, fájl / link), pénztár-értesítők, nyitóoldal SEO, számlatételek, termékinformációk alapértékei';
            default:
                return null;
        }
    }

    protected static function thumb(string $type, $record): ?string
    {
        return match ($type) {
            'product' => $record->primary_image_thumb ?: $record->primary_image,
            'gallery_image' => $record->image_thumb ?: $record->image,
            default => null,
        };
    }

    public static function adminUrl(string $type, $record): ?string
    {
        $id = (int) $record->getKey();

        return match ($type) {
            'product' => route('admin.webshop.products.edit', $id),
            'category' => route('admin.webshop.categories.edit', $id),
            'property_category' => route('admin.webshop.property-categories.edit', $id),
            'property' => route('admin.webshop.properties.index', $record->property_category_id),
            'label' => route('admin.webshop.labels.edit', $id),
            'gallery_image', 'document' => $record->product_id ? route('admin.webshop.products.edit', $record->product_id) : null,
            'custom_content' => route('admin.webshop.extra-settings.custom-contents.index'),
            'home_block' => route('admin.webshop.extra-settings.home-page.index'),
            'setting' => route('admin.webshop.extra-settings.index'),
            default => null,
        };
    }

    /** A vásárlói oldal címe nyelvenként (termék, kategória) — az ellenőrzéshez */
    protected static function siteUrls(string $type, $record, array $locales): array
    {
        if (!in_array($type, ['product', 'category'], true)) {
            return [];
        }

        $urls = [];
        foreach ($locales as $locale) {
            try {
                $urls[$locale] = $type === 'product' ? WebshopUrl::product($record, $locale) : WebshopUrl::category($record, $locale);
            } catch (\Throwable $e) {
                // az admin domainről a site-útvonal nem mindig generálható (más domain) — a link ilyenkor kimarad
            }
        }

        return $urls;
    }
}
