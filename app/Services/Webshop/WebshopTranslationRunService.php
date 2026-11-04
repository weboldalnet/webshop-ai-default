<?php

namespace Weboldalnet\WebshopAiDefault\Services\Webshop;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Weboldalnet\WebshopAiDefault\Models\WebshopTranslation;
use Weboldalnet\WebshopAiDefault\Models\WebshopTranslationBackup;
use Weboldalnet\WebshopAiDefault\Models\WebshopTranslationRun;
use Weboldalnet\WebshopAiDefault\Models\WebshopTranslationRunItem;

/**
 * AI-fordítás futása: a „Webshop › Fordítások” tömeges fordítása és a szerkesztők „Fordítás” gombja (egy tételes
 * futás) ugyanezt használja.
 *
 * Menet (a core fordítójának stílusában, böngészőből vezérelve): createRun() → a böngésző step()-et hív, amíg
 * van függő tétel; egy lépés = egy tétel (egy rekord egy nyelven, 1–2 AI-hívás). Az állapot az adatbázisban van,
 * így a futás szüneteltethető, bezárt fül vagy elfogyott kredit után folytatható. Az eredmény azonnal élesedik;
 * a felülírt értékek mentésbe kerülnek (webshop_translation_backups), a futás vagy egy tétel visszavonható.
 *
 * Az AI-hívás, a HTML-védelem és az ellenőrzés a core App\Services\Translation\AiTranslator-a — ha a projekt
 * core-jában nincs meg (vagy nincs bekötött AI), a funkció rejtve marad (isAvailable()).
 */
class WebshopTranslationRunService
{
    public const MODE_UNTRANSLATED = 'untranslated';
    public const MODE_CHANGES = 'changes';
    public const MODE_ALL = 'all';

    public const MODES = [
        self::MODE_UNTRANSLATED => 'Csak a még fordítatlan mezők',
        self::MODE_CHANGES => 'Fordítatlan és elavult mezők (az eredeti azóta változott)',
        self::MODE_ALL => 'Minden újrafordítása',
    ];

    /** A slug melyik (lefordított) mezőből képződik — ugyanaz, amiből az alapnyelvi slug (WebshopSlugService) */
    public const SLUG_SOURCE_FIELDS = [
        'product' => 'name',
        'category' => 'name_singular',
        'property_category' => 'name',
        'property' => 'name',
    ];

    /** Az összesítőben és a futásban kezelt típusok (a `ui` a nyelvi fájlokból jön, a `setting` record_id 0) */
    public const RUN_TYPES = [
        'product', 'category', 'property_category', 'property', 'label', 'gallery_image', 'document',
        'custom_content', 'home_block', 'setting',
    ];

    /** Többnyelvű projekt, a core fordítója megvan és az AI be van kötve */
    public static function isAvailable(): bool
    {
        if (!WebshopTranslationService::isMultiLanguage() || !WebshopTranslationService::targetLocales()) {
            return false;
        }
        if (!class_exists(\App\Services\Translation\AiTranslator::class) || !class_exists(\App\Services\Translation\ContentTranslationService::class)) {
            return false;
        }

        try {
            return app(\App\Services\Translation\ContentTranslationService::class)->isAvailable();
        } catch (\Throwable $e) {
            return false;
        }
    }

    // ------------------------------------------------------------------------------------------------ rekordok

    public static function modelClass(string $type): ?string
    {
        return WebshopTranslationService::TYPES[$type]['model'] ?? null;
    }

    public static function findRecord(string $type, int $recordId)
    {
        if ($type === 'setting') {
            return WebshopTranslationStatusService::settingRecord();
        }

        $class = self::modelClass($type);

        return $class ? $class::query()->find($recordId) : null;
    }

    /** Egy rekord olvasható neve a listákhoz és a riporthoz */
    public static function recordLabel(string $type, $record): string
    {
        if ($type === 'setting') {
            return 'Webshop-szövegek (beállítások)';
        }
        if (!$record instanceof Model) {
            return '—';
        }

        foreach (['name', 'name_singular', 'title', 'alt', 'type'] as $field) {
            $value = $record->untranslated($field);
            if (is_string($value) && trim($value) !== '') {
                return \Illuminate\Support\Str::limit(trim(strip_tags($value)), 80);
            }
        }

        return '#' . $record->getKey();
    }

    // ------------------------------------------------------------------------------------------- tervezés

    /**
     * A tételben fordítandó mezők: [mező => forrásszöveg] a mód, a csoportok és a „kézit ne írja felül” szerint.
     * A slug nincs benne (az a lefordított névből képződik) — arról a második visszatérési érték dönt.
     *
     * @return array{0: array<string, string>, 1: bool} [szövegek, kell-e slug]
     */
    public static function planItem(string $type, $record, string $locale, array $options): array
    {
        $mode = $options['mode'] ?? self::MODE_UNTRANSLATED;
        $groups = $options['groups'] ?? array_keys(WebshopTranslationStatusService::GROUPS);
        $keepManual = (bool) ($options['keep_manual'] ?? true);

        $sources = WebshopTranslationStatusService::sourceFields($type, $record);
        $rows = WebshopTranslationStatusService::translationRows($type, [(int) $record->getKey()], [$locale])[(int) $record->getKey()][$locale] ?? [];

        $texts = [];
        $needsSlug = false;
        foreach ($sources as $field => $source) {
            $group = WebshopTranslationStatusService::fieldGroup($field);
            if (!in_array($group, $groups, true)) {
                continue;
            }

            $row = $rows[$field] ?? null;
            $state = WebshopTranslationStatusService::stateOf($source, $row);
            $isManual = $row !== null && $row['origin'] === WebshopTranslation::ORIGIN_MANUAL && $state !== WebshopTranslationStatusService::STATE_MISSING;

            if ($keepManual && $isManual) {
                continue;
            }
            if ($mode === self::MODE_UNTRANSLATED && $state !== WebshopTranslationStatusService::STATE_MISSING) {
                continue;
            }
            if ($mode === self::MODE_CHANGES && $state === WebshopTranslationStatusService::STATE_DONE) {
                continue;
            }

            if ($group === WebshopTranslationStatusService::GROUP_SLUG) {
                $needsSlug = isset(self::SLUG_SOURCE_FIELDS[$type]);
                continue;
            }

            $texts[$field] = $source;
        }

        return [$texts, $needsSlug];
    }

    /**
     * Becslés a kijelölésre: tételek, mezők, karakterek, AI-hívások (a core darabolása szerint).
     *
     * @param array<string, int[]> $selection típus => rekord-azonosítók
     */
    public static function estimate(array $selection, array $options): array
    {
        $estimate = ['items' => 0, 'fields' => 0, 'characters' => 0, 'ai_calls' => 0, 'slugs' => 0];
        $class = class_exists(\App\Services\Translation\AiTranslator::class) ? \App\Services\Translation\AiTranslator::class : null;

        foreach ($selection as $type => $ids) {
            foreach (self::recordsFor($type, $ids) as $record) {
                foreach ($options['locales'] ?? [] as $locale) {
                    [$texts, $needsSlug] = self::planItem($type, $record, $locale, $options);
                    if (!$texts && !$needsSlug) {
                        continue;
                    }
                    $estimate['items']++;
                    $estimate['fields'] += count($texts);
                    $estimate['slugs'] += $needsSlug ? 1 : 0;
                    $estimate['characters'] += array_sum(array_map('mb_strlen', $texts));
                    $estimate['ai_calls'] += $texts ? ($class ? count($class::chunks($texts)) : 1) : 0;
                }
            }
        }

        // tapasztalati érték: egy AI-hívás 4–12 mp
        $estimate['seconds'] = $estimate['ai_calls'] * 8;

        return $estimate;
    }

    /** @return iterable<Model|object> */
    public static function recordsFor(string $type, array $ids): iterable
    {
        if ($type === 'setting') {
            return [WebshopTranslationStatusService::settingRecord()];
        }

        $class = self::modelClass($type);
        if (!$class || !$ids) {
            return [];
        }

        return $class::query()->whereIn('id', array_map('intval', $ids))->orderBy('id')->get();
    }

    // ----------------------------------------------------------------------------------------------- futás

    /**
     * Új futás a kijelölésre (típus => azonosítók), a megadott beállításokkal. A tétel = rekord × nyelv; a
     * fordítanivaló nélküli tételek nem kerülnek bele.
     */
    public static function createRun(array $selection, array $options, ?int $adminId, string $scopeLabel): WebshopTranslationRun
    {
        $options = self::normalizeOptions($options);

        return WebshopTranslationService::db()->transaction(function () use ($selection, $options, $adminId, $scopeLabel) {
            $run = WebshopTranslationRun::query()->create([
                'admin_id' => $adminId,
                'status' => WebshopTranslationRun::STATUS_PENDING,
                'options' => $options,
                'scope_label' => mb_substr($scopeLabel, 0, 255),
            ]);

            $total = 0;
            foreach ($selection as $type => $ids) {
                if (!in_array($type, self::RUN_TYPES, true)) {
                    continue;
                }
                foreach (self::recordsFor($type, $ids) as $record) {
                    foreach ($options['locales'] as $locale) {
                        [$texts, $needsSlug] = self::planItem($type, $record, $locale, $options);
                        if (!$texts && !$needsSlug) {
                            continue;
                        }
                        WebshopTranslationRunItem::query()->create([
                            'run_id' => $run->id,
                            'type' => $type,
                            'record_id' => (int) $record->getKey(),
                            'locale' => $locale,
                            'status' => WebshopTranslationRunItem::STATUS_PENDING,
                        ]);
                        $total++;
                    }
                }
            }

            $run->update(['total_items' => $total, 'status' => $total ? WebshopTranslationRun::STATUS_PENDING : WebshopTranslationRun::STATUS_DONE]);

            return $run;
        });
    }

    public static function normalizeOptions(array $options): array
    {
        $locales = array_values(array_intersect((array) ($options['locales'] ?? []), WebshopTranslationService::targetLocales()));
        $groups = array_values(array_intersect((array) ($options['groups'] ?? []), array_keys(WebshopTranslationStatusService::GROUPS)));

        return [
            'locales' => $locales,
            'mode' => array_key_exists($options['mode'] ?? '', self::MODES) ? $options['mode'] : self::MODE_UNTRANSLATED,
            'groups' => $groups ?: [WebshopTranslationStatusService::GROUP_TEXTS],
            'keep_manual' => filter_var($options['keep_manual'] ?? true, FILTER_VALIDATE_BOOLEAN),
        ];
    }

    /**
     * Egy lépés: a következő függő tétel lefordítása. Elfogyott kreditnél a futás szünetel (a tétel függő marad,
     * később folytatható); más hibánál a tétel hibás lesz, a futás megy tovább.
     */
    public static function step(WebshopTranslationRun $run, ?int $adminId): array
    {
        if ($run->isFinished()) {
            return self::progress($run->fresh());
        }

        @set_time_limit(180);

        $item = $run->items()->where('status', WebshopTranslationRunItem::STATUS_PENDING)->orderBy('id')->first();
        if (!$item) {
            $run->update(['status' => WebshopTranslationRun::STATUS_DONE, 'finished_at' => now()]);

            return self::progress($run->fresh());
        }

        $run->update(['status' => WebshopTranslationRun::STATUS_RUNNING, 'error' => null]);

        try {
            $result = self::processItem($run, $item, $adminId);
        } catch (\Throwable $e) {
            if (class_exists(\App\Ai\Gateway\AiGatewayOutOfCreditsException::class) && $e instanceof \App\Ai\Gateway\AiGatewayOutOfCreditsException) {
                $run->update(['status' => WebshopTranslationRun::STATUS_PAUSED, 'error' => 'Elfogyott az AI-kredit. A futás szünetel; kredit-feltöltés után folytatható.']);

                return self::progress($run->fresh(), ['paused' => true]);
            }

            Log::error('Webshop AI-fordítás: hibás tétel', ['run' => $run->id, 'item' => $item->id, 'error' => $e->getMessage()]);
            $item->update(['status' => WebshopTranslationRunItem::STATUS_ERROR, 'message' => mb_substr($e->getMessage(), 0, 500)]);
            $result = ['fields' => 0, 'characters' => 0, 'ai_calls' => 0];
        }

        $item->refresh();
        $run->increment('translated_fields', $result['fields']);
        $run->increment('characters', $result['characters']);
        $run->increment('ai_calls', $result['ai_calls']);
        self::recount($run);

        if (!$run->items()->where('status', WebshopTranslationRunItem::STATUS_PENDING)->exists()) {
            $run->update(['status' => WebshopTranslationRun::STATUS_DONE, 'finished_at' => now()]);
        }

        return self::progress($run->fresh(), ['item' => self::itemSummary($item)]);
    }

    /** @return array{fields: int, characters: int, ai_calls: int} */
    protected static function processItem(WebshopTranslationRun $run, WebshopTranslationRunItem $item, ?int $adminId): array
    {
        $record = self::findRecord($item->type, $item->record_id);
        if (!$record) {
            $item->update(['status' => WebshopTranslationRunItem::STATUS_SKIPPED, 'message' => 'A tétel azóta törlődött.']);

            return ['fields' => 0, 'characters' => 0, 'ai_calls' => 0];
        }

        [$texts, $needsSlug] = self::planItem($item->type, $record, $item->locale, $run->options ?? []);
        if (!$texts && !$needsSlug) {
            $item->update(['status' => WebshopTranslationRunItem::STATUS_SKIPPED, 'message' => 'Nincs fordítanivaló (azóta lefordították).']);

            return ['fields' => 0, 'characters' => 0, 'ai_calls' => 0];
        }

        $translator = app(\App\Services\Translation\AiTranslator::class);
        $context = trim(config('app.name') . ' – webshop, ' . (WebshopTranslationService::TYPES[$item->type]['label'] ?? $item->type)
            . ': ' . self::recordLabel($item->type, $record));

        $done = 0;
        $errors = [];
        $characters = 0;
        $calls = 0;
        $translatedValues = [];

        foreach ((class_exists(\App\Services\Translation\AiTranslator::class) ? \App\Services\Translation\AiTranslator::chunks($texts) : [array_keys($texts)]) as $keys) {
            $chunk = array_intersect_key($texts, array_flip($keys));
            $characters += array_sum(array_map('mb_strlen', $chunk));
            $calls++;

            $results = $translator->translate($chunk, WebshopTranslationService::defaultLocale(), $item->locale, $context);

            foreach ($chunk as $field => $source) {
                $value = $results[$field]['value'] ?? null;
                if ($value === null || trim($value) === '') {
                    $errors[] = $field . ': ' . ($results[$field]['error'] ?? 'nincs fordítás');
                    continue;
                }
                self::write($run, $item, $field, $value, $source, $adminId);
                $translatedValues[$field] = $value;
                $done++;
            }
        }

        if ($needsSlug) {
            $nameField = self::SLUG_SOURCE_FIELDS[$item->type];
            $name = $translatedValues[$nameField]
                ?? WebshopTranslationService::get($item->type, (int) $item->record_id, $item->locale, $nameField);
            if ($name) {
                $slug = WebshopSlugService::generateUniqueTranslatedSlug($name, $item->type, $item->locale, (int) $item->record_id);
                self::write($run, $item, 'slug', $slug, (string) $record->untranslated('slug'), $adminId);
                $done++;
            }
        }

        $item->update([
            'status' => $done === 0 && $errors ? WebshopTranslationRunItem::STATUS_ERROR : WebshopTranslationRunItem::STATUS_DONE,
            'fields_done' => $done,
            'fields_error' => count($errors),
            'message' => $errors ? mb_substr(implode(' | ', $errors), 0, 1000) : null,
        ]);

        return ['fields' => $done, 'characters' => $characters, 'ai_calls' => $calls];
    }

    /** Fordítás írása mentéssel: a régi érték a visszavonáshoz, az új hash-e az utólagos módosítás felismeréséhez */
    protected static function write(WebshopTranslationRun $run, WebshopTranslationRunItem $item, string $field, string $value, string $source, ?int $adminId): void
    {
        $current = WebshopTranslation::query()
            ->where(['type' => $item->type, 'record_id' => $item->record_id, 'locale' => $item->locale, 'field' => $field])
            ->first();

        WebshopTranslationBackup::query()->create([
            'run_id' => $run->id,
            'run_item_id' => $item->id,
            'type' => $item->type,
            'record_id' => $item->record_id,
            'locale' => $item->locale,
            'field' => $field,
            'old_value' => $current?->value,
            'old_source_hash' => $current?->source_hash,
            'old_origin' => $current?->origin,
            'new_value_hash' => sha1($value),
            'admin_id' => $adminId,
        ]);

        WebshopTranslationService::set($item->type, (int) $item->record_id, $item->locale, $field, $value, $source, WebshopTranslation::ORIGIN_AI, $adminId);
    }

    // ------------------------------------------------------------------------------------------ visszavonás

    /**
     * Egy tétel visszavonása: a futás előtti értékek vissza (ahol nem volt, a fordítás törlődik). Azt a mezőt, amit
     * azóta valaki átírt (a hash nem egyezik), nem bántja. @return int visszaállított mezők
     */
    public static function restoreItem(WebshopTranslationRunItem $item): int
    {
        $restored = 0;

        WebshopTranslationService::db()->transaction(function () use ($item, &$restored) {
            $backups = WebshopTranslationBackup::query()->where('run_item_id', $item->id)->orderByDesc('id')->get();
            foreach ($backups as $backup) {
                $current = WebshopTranslation::query()
                    ->where(['type' => $backup->type, 'record_id' => $backup->record_id, 'locale' => $backup->locale, 'field' => $backup->field])
                    ->first();
                if ($current && $backup->new_value_hash && sha1((string) $current->value) !== $backup->new_value_hash) {
                    continue;
                }

                if ($backup->old_value === null) {
                    $current?->delete();
                } else {
                    WebshopTranslation::query()->updateOrCreate(
                        ['type' => $backup->type, 'record_id' => $backup->record_id, 'locale' => $backup->locale, 'field' => $backup->field],
                        ['value' => $backup->old_value, 'source_hash' => $backup->old_source_hash, 'origin' => $backup->old_origin ?: WebshopTranslation::ORIGIN_MANUAL]
                    );
                }
                $restored++;
            }

            $item->update(['status' => WebshopTranslationRunItem::STATUS_RESTORED]);
        });

        WebshopTranslationService::flushCache();

        return $restored;
    }

    /** A teljes futás visszavonása (a lefutott tételek). @return int visszaállított mezők */
    public static function restoreRun(WebshopTranslationRun $run): int
    {
        $restored = 0;
        foreach ($run->items()->whereIn('status', [WebshopTranslationRunItem::STATUS_DONE, WebshopTranslationRunItem::STATUS_ERROR])->get() as $item) {
            $restored += self::restoreItem($item);
        }

        if ($run->status !== WebshopTranslationRun::STATUS_DONE) {
            $run->update(['status' => WebshopTranslationRun::STATUS_CANCELLED, 'finished_at' => now()]);
        }
        self::recount($run);

        return $restored;
    }

    /**
     * Szünet: a „Szünet” gomb, illetve futás közben bezárt ablak vagy fül. A függő tételek maradnak, a futás a
     * listából („Riport”) folytatható. Lezárt futáson nem változtat.
     */
    public static function pause(WebshopTranslationRun $run): void
    {
        if (!$run->isFinished()) {
            $run->update(['status' => WebshopTranslationRun::STATUS_PAUSED]);
        }
    }

    public static function cancel(WebshopTranslationRun $run): void
    {
        $run->items()->where('status', WebshopTranslationRunItem::STATUS_PENDING)
            ->update(['status' => WebshopTranslationRunItem::STATUS_SKIPPED, 'message' => 'A futást leállították.']);
        $run->update(['status' => WebshopTranslationRun::STATUS_CANCELLED, 'finished_at' => now()]);
        self::recount($run);
    }

    // ------------------------------------------------------------------------------------------- állapot

    protected static function recount(WebshopTranslationRun $run): void
    {
        $counts = $run->items()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $run->update([
            'done_items' => (int) ($counts[WebshopTranslationRunItem::STATUS_DONE] ?? 0),
            'error_items' => (int) ($counts[WebshopTranslationRunItem::STATUS_ERROR] ?? 0),
            'skipped_items' => (int) ($counts[WebshopTranslationRunItem::STATUS_SKIPPED] ?? 0) + (int) ($counts[WebshopTranslationRunItem::STATUS_RESTORED] ?? 0),
        ]);
    }

    public static function progress(WebshopTranslationRun $run, array $extra = []): array
    {
        $pending = $run->items()->where('status', WebshopTranslationRunItem::STATUS_PENDING)->count();

        return array_merge([
            'run_id' => $run->id,
            'status' => $run->status,
            'total' => (int) $run->total_items,
            'processed' => (int) $run->total_items - $pending,
            'done' => (int) $run->done_items,
            'errors' => (int) $run->error_items,
            'skipped' => (int) $run->skipped_items,
            'fields' => (int) $run->translated_fields,
            'ai_calls' => (int) $run->ai_calls,
            'finished' => $run->isFinished(),
            'error' => $run->error,
        ], $extra);
    }

    public static function itemSummary(WebshopTranslationRunItem $item): array
    {
        $record = self::findRecord($item->type, $item->record_id);

        return [
            'id' => $item->id,
            'type' => $item->type,
            'type_label' => WebshopTranslationService::TYPES[$item->type]['label'] ?? $item->type,
            'record_id' => $item->record_id,
            'label' => self::recordLabel($item->type, $record),
            'locale' => $item->locale,
            'status' => $item->status,
            'fields_done' => $item->fields_done,
            'fields_error' => $item->fields_error,
            'message' => $item->message,
        ];
    }
}
