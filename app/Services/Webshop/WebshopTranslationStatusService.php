<?php

namespace Weboldalnet\WebshopAiDefault\Services\Webshop;

use Illuminate\Database\Eloquent\Model;
use Weboldalnet\WebshopAiDefault\Models\WebshopTranslation;

/**
 * Mi van lefordítva: a fordítható tartalom forrás-mezői és az egyes nyelvek állapota mezőnként.
 *
 * Állapotok (a core fordítójával azonos elv): `missing` — nincs fordítás; `outdated` — van, de az eredeti szöveg a
 * fordítás óta változott (forrás-hash); `done` — naprakész. A kézzel javított (origin = manual) fordítást az
 * AI-futás alapból nem írja felül.
 *
 * Csoportok (a futás „Tartalmak” beállítása): `texts` (név, leírás, jogi infó, alt-szöveg …), `seo` (og_title,
 * og_description), `slug` (a cím szava — a lefordított névből képződik, nem az AI fordítja).
 */
class WebshopTranslationStatusService
{
    public const STATE_MISSING = 'missing';
    public const STATE_OUTDATED = 'outdated';
    public const STATE_DONE = 'done';

    public const GROUP_TEXTS = 'texts';
    public const GROUP_SEO = 'seo';
    public const GROUP_SLUG = 'slug';

    public const GROUPS = [
        self::GROUP_TEXTS => 'Szövegek (név, leírás, jogi információk, képek alt-szövege)',
        self::GROUP_SEO => 'SEO-adatok (megosztási cím és leírás)',
        self::GROUP_SLUG => 'Címek (URL-szó a lefordított névből)',
    ];

    private const SEO_FIELDS = ['og_title', 'og_description'];

    /** A jogi információk kódszerű, nem fordítandó szövegmezői */
    private const LEGAL_INFO_CODE_FIELDS = ['identifier', 'ufi', 'alcohol'];

    /** A nyitóoldal-blokkok JSON-jában a szöveges kulcsok (a core FieldClassifier listáján felül) */
    private const HOME_BLOCK_TEXT_KEYS = ['content', 'button_text', 'link_label'];

    public static function fieldGroup(string $field): string
    {
        $root = explode('.', $field)[0];
        if ($root === 'slug') {
            return self::GROUP_SLUG;
        }

        return in_array($root, self::SEO_FIELDS, true) ? self::GROUP_SEO : self::GROUP_TEXTS;
    }

    /** Beállítás-típusnál (record_id 0) egy „rekord”: a beállítás-szövegek összessége */
    public static function settingRecord(): object
    {
        return new class {
            public function getKey(): int
            {
                return 0;
            }
        };
    }

    /**
     * A rekord fordítandó, nem üres forrás-mezői: [mező => szöveg]. JSON-mezőnél a szöveges levelek pont-útvonallal
     * (legal_info.general.warnings). A slug forrása a név (a slug a lefordított névből képződik).
     *
     * @return array<string, string>
     */
    public static function sourceFields(string $type, $record): array
    {
        $fields = WebshopTranslationService::fields($type);
        $sources = [];

        foreach ($fields as $field => $kind) {
            $value = $type === 'setting'
                ? WebshopSettingsService::get($field)
                : ($record instanceof Model ? ($record->untranslated($field) ?? null) : null);

            if ($kind === 'json') {
                foreach (self::jsonTextLeaves($type, $field, is_array($value) ? $value : (json_decode((string) $value, true) ?: [])) as $path => $text) {
                    $sources[$field . '.' . $path] = $text;
                }
                continue;
            }

            if (is_string($value) && trim(strip_tags($value)) !== '') {
                $sources[$field] = $value;
            }
        }

        return $sources;
    }

    /**
     * Mezőnkénti állapot egy nyelven: [mező => ['state', 'origin', 'value', 'source']].
     *
     * @return array<string, array{state: string, origin: ?string, value: ?string, source: string}>
     */
    public static function fieldStates(string $type, $record, string $locale, ?array $sources = null): array
    {
        $sources = $sources ?? self::sourceFields($type, $record);
        $rows = self::translationRows($type, [(int) $record->getKey()], [$locale])[(int) $record->getKey()][$locale] ?? [];

        $states = [];
        foreach ($sources as $field => $source) {
            $row = $rows[$field] ?? null;
            $states[$field] = [
                'state' => self::stateOf($source, $row),
                'origin' => $row['origin'] ?? null,
                'value' => $row['value'] ?? null,
                'source' => $source,
            ];
        }

        return $states;
    }

    /** @param array{value: ?string, source_hash: ?string}|null $row */
    public static function stateOf(string $source, ?array $row): string
    {
        if ($row === null || $row['value'] === null || trim((string) $row['value']) === '') {
            return self::STATE_MISSING;
        }

        // a slug a névből képződik: nem avul el attól, hogy a név változott (a cím maradjon stabil)
        if (($row['field'] ?? null) === 'slug') {
            return self::STATE_DONE;
        }

        return $row['source_hash'] === WebshopTranslationService::hash($source) ? self::STATE_DONE : self::STATE_OUTDATED;
    }

    /**
     * Összesítés: ['total', 'done', 'missing', 'outdated', 'manual'] — a mezők száma.
     *
     * @param array<string, array{state: string, origin: ?string}> $states
     */
    public static function summarize(array $states): array
    {
        $summary = ['total' => 0, 'done' => 0, 'missing' => 0, 'outdated' => 0, 'manual' => 0];
        foreach ($states as $state) {
            $summary['total']++;
            $summary[$state['state']]++;
            if ($state['origin'] === WebshopTranslation::ORIGIN_MANUAL && $state['state'] !== self::STATE_MISSING) {
                $summary['manual']++;
            }
        }

        return $summary;
    }

    /** Egy rekord-nyelv összegzéséből a lista-állapot: kész / részben / elavult / hiányzik / nincs mit fordítani */
    public static function overallState(array $summary): string
    {
        if ($summary['total'] === 0) {
            return 'empty';
        }
        if ($summary['done'] === $summary['total']) {
            return 'done';
        }
        if ($summary['missing'] === $summary['total']) {
            return 'missing';
        }

        return $summary['outdated'] > 0 ? 'outdated' : 'partial';
    }

    /**
     * Fordítás-sorok több rekordra és nyelvre egy lekérdezéssel: [record_id][locale][field] => ['value', 'source_hash', 'origin', 'field', 'updated_at'].
     */
    public static function translationRows(string $type, array $recordIds, array $locales): array
    {
        $rows = [];
        if (!$recordIds || !$locales) {
            return $rows;
        }

        WebshopTranslation::query()
            ->where('type', $type)
            ->whereIn('locale', $locales)
            ->whereIn('record_id', array_values(array_unique(array_map('intval', $recordIds))))
            ->get(['record_id', 'locale', 'field', 'value', 'source_hash', 'origin', 'updated_at'])
            ->each(function (WebshopTranslation $row) use (&$rows) {
                $rows[$row->record_id][$row->locale][$row->field] = [
                    'field' => $row->field,
                    'value' => $row->value,
                    'source_hash' => $row->source_hash,
                    'origin' => $row->origin,
                    'updated_at' => $row->updated_at,
                ];
            });

        return $rows;
    }

    /**
     * A JSON-mező szöveges levelei: [pont-útvonal => szöveg]. A jogi információknál a profil-definíció dönt (szöveges
     * mezők, a kódszerűek nélkül); a többi JSON-nál a core FieldClassifier (ha van), különben a kulcsnév.
     */
    public static function jsonTextLeaves(string $type, string $field, array $data): array
    {
        $leaves = [];

        if ($type === 'product' && $field === 'legal_info') {
            foreach (WebshopProductLegalInfoService::PROFILES as $profile => $definition) {
                foreach ($definition['fields'] as $key => $config) {
                    $value = $data[$profile][$key] ?? null;
                    if (in_array($config['type'], ['text', 'textarea'], true)
                        && !in_array($key, self::LEGAL_INFO_CODE_FIELDS, true)
                        && is_string($value) && trim($value) !== '' && preg_match('/\p{L}/u', $value)) {
                        $leaves[$profile . '.' . $key] = $value;
                    }
                }
            }

            return $leaves;
        }

        $classifier = class_exists(\App\Services\Translation\FieldClassifier::class)
            ? new \App\Services\Translation\FieldClassifier(self::HOME_BLOCK_TEXT_KEYS, ['items', 'checks'])
            : null;

        $walk = function (array $node, array $path) use (&$walk, &$leaves, $classifier, $field) {
            foreach ($node as $key => $value) {
                $current = array_merge($path, [$key]);
                if (is_array($value)) {
                    $walk($value, $current);
                    continue;
                }
                $isText = $classifier
                    ? $classifier->classify(array_merge([$field], $current), $value) === \App\Services\Translation\FieldClassifier::TEXT
                    : (is_string($value) && !is_int($key) && in_array($key, array_merge(['title', 'text', 'label', 'alt', 'description'], self::HOME_BLOCK_TEXT_KEYS), true)
                        && preg_match('/\p{L}/u', strip_tags($value)));
                if ($isText) {
                    $leaves[implode('.', $current)] = (string) $value;
                }
            }
        };
        $walk($data, []);

        return $leaves;
    }
}
