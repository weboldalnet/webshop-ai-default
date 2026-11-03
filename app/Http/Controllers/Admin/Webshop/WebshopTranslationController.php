<?php

namespace Weboldalnet\WebshopAiDefault\Http\Controllers\Admin\Webshop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Weboldalnet\WebshopAiDefault\Models\WebshopCategory;
use Weboldalnet\WebshopAiDefault\Models\WebshopTranslation;
use Weboldalnet\WebshopAiDefault\Models\WebshopTranslationRun;
use Weboldalnet\WebshopAiDefault\Models\WebshopTranslationRunItem;
use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopSlugService;
use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopTranslationOverviewService;
use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopTranslationRunService;
use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopTranslationService;
use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopTranslationStatusService;

/**
 * Webshop › Fordítások: összesítő, mezőnkénti fordítás-szerkesztő, AI-fordítás (tömeges és rekordonkénti).
 * Csak többnyelvű projektben érhető el; az AI-gombok csak bekötött AI mellett (WebshopTranslationRunService::isAvailable).
 */
class WebshopTranslationController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            abort_unless(WebshopTranslationService::isMultiLanguage() && WebshopTranslationService::targetLocales(), 404);

            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $filters = WebshopTranslationOverviewService::normalizeFilters($request->all());
        $locales = WebshopTranslationService::targetLocales();

        $rows = WebshopTranslationOverviewService::filterByState(
            WebshopTranslationOverviewService::rows($filters['type'], WebshopTranslationOverviewService::records($filters['type'], $filters), $locales),
            $filters['locale'],
            $filters['state']
        );
        $paginator = WebshopTranslationOverviewService::paginate($rows, $filters['page'], route('admin.webshop.translations.index'), $filters);

        $data = [
            'filters' => $filters,
            'locales' => $locales,
            'localeNames' => self::localeNames(),
            'rows' => $paginator,
            'total' => count($rows),
            'aiAvailable' => WebshopTranslationRunService::isAvailable(),
        ];

        if ($request->ajax()) {
            return response()->json([
                'rows' => view('admin.webshop.translations.partials.rows', $data)->render(),
                'pagination' => (string) $paginator->links(),
                'total' => count($rows),
            ]);
        }

        return view('admin.webshop.translations.index', $data + [
            'types' => WebshopTranslationOverviewService::types(),
            'states' => WebshopTranslationOverviewService::STATES,
            'categories' => WebshopCategory::query()->orderBy('name_singular')->get(['id', 'name_singular', 'parent_id']),
            'kpis' => WebshopTranslationOverviewService::kpis($locales),
            'runs' => WebshopTranslationRun::query()->latest('id')->limit(8)->get(),
            'hidesUntranslated' => WebshopTranslationService::hidesUntranslated(),
        ]);
    }

    // ----------------------------------------------------------------------------------- kézi szerkesztés

    public function edit(string $type, int $recordId, string $locale)
    {
        [$record, $locale] = $this->resolve($type, $recordId, $locale);
        $sources = WebshopTranslationStatusService::sourceFields($type, $record);

        return view('admin.webshop.translations.edit', [
            'type' => $type,
            'typeLabel' => WebshopTranslationService::TYPES[$type]['label'] ?? $type,
            'record' => $record,
            'recordId' => $recordId,
            'label' => WebshopTranslationRunService::recordLabel($type, $record),
            'locale' => $locale,
            'locales' => WebshopTranslationService::targetLocales(),
            'localeNames' => self::localeNames(),
            'fields' => WebshopTranslationStatusService::fieldStates($type, $record, $locale, $sources),
            'kinds' => WebshopTranslationService::fields($type),
            'adminUrl' => WebshopTranslationOverviewService::adminUrl($type, $record),
            'aiAvailable' => WebshopTranslationRunService::isAvailable(),
        ]);
    }

    /**
     * A kézzel írt fordítások mentése (origin = manual: az AI-futás alapból nem írja felül). A változatlanul hagyott
     * mezőhöz nem nyúl (megmarad az eredete és a forrás-hash-e); az üresre törölt mező fordítása törlődik.
     */
    public function update(Request $request, string $type, int $recordId, string $locale)
    {
        [$record, $locale] = $this->resolve($type, $recordId, $locale);
        $sources = WebshopTranslationStatusService::sourceFields($type, $record);
        $current = WebshopTranslationStatusService::fieldStates($type, $record, $locale, $sources);
        $input = (array) $request->input('fields', []);
        $adminId = auth('admin')->id();
        $changed = 0;

        foreach ($sources as $field => $source) {
            if (!array_key_exists(self::inputKey($field), $input)) {
                continue;
            }
            $value = $input[self::inputKey($field)];
            $value = is_string($value) ? trim($value) : '';
            $old = (string) ($current[$field]['value'] ?? '');

            // a „naprakésznek jelölöm” kapcsoló: változatlan szöveg, de az eredeti azóta módosult → új forrás-hash
            $confirm = in_array(self::inputKey($field), (array) $request->input('confirm', []), true);

            if ($value === $old && !$confirm) {
                continue;
            }

            if ($field === 'slug' && $value !== '') {
                $value = WebshopSlugService::generateUniqueTranslatedSlug($value, $type, $locale, $recordId);
            }

            WebshopTranslationService::set($type, $recordId, $locale, $field, $value, $source, WebshopTranslation::ORIGIN_MANUAL, $adminId);
            $changed++;
        }

        $message = $changed ? "A fordítás elmentve ({$changed} mező)." : 'Nem változott semmi.';

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return redirect()->route('admin.webshop.translations.edit', [$type, $recordId, $locale])->with('success', $message);
    }

    // ---------------------------------------------------------------------------------------- AI-futás

    /** A tömeges / rekordonkénti AI-fordítás párbeszédablakának törzse */
    public function dialog(Request $request)
    {
        abort_unless(WebshopTranslationRunService::isAvailable(), 404);
        $selection = $this->selection($request);

        return response()->json(['html' => view('admin.webshop.translations.partials.dialog', [
            'selection' => $selection,
            'selectionLabel' => $this->selectionLabel($selection),
            'locales' => WebshopTranslationService::targetLocales(),
            'localeNames' => self::localeNames(),
            'modes' => WebshopTranslationRunService::MODES,
            'groups' => WebshopTranslationStatusService::GROUPS,
            'preselectLocale' => $request->input('locale'),
        ])->render()]);
    }

    public function estimate(Request $request)
    {
        abort_unless(WebshopTranslationRunService::isAvailable(), 404);
        $selection = $this->selection($request);
        $options = WebshopTranslationRunService::normalizeOptions((array) $request->input('options', []));

        return response()->json(WebshopTranslationRunService::estimate($selection, $options));
    }

    public function storeRun(Request $request)
    {
        abort_unless(WebshopTranslationRunService::isAvailable(), 404);
        $selection = $this->selection($request);
        $options = WebshopTranslationRunService::normalizeOptions((array) $request->input('options', []));

        if (!$options['locales']) {
            return response()->json(['success' => false, 'message' => 'Jelölj ki legalább egy nyelvet.'], 422);
        }

        $run = WebshopTranslationRunService::createRun($selection, $options, auth('admin')->id(), $this->selectionLabel($selection));

        return response()->json(['success' => true, 'urls' => self::runUrls($run)] + WebshopTranslationRunService::progress($run));
    }

    public function step(WebshopTranslationRun $run)
    {
        abort_unless(WebshopTranslationRunService::isAvailable(), 404);

        return response()->json(['urls' => self::runUrls($run)] + WebshopTranslationRunService::step($run, auth('admin')->id()));
    }

    /** A futás végpontjai a böngészőnek (lépés, leállítás, visszavonás, riport) */
    private static function runUrls(WebshopTranslationRun $run): array
    {
        return [
            'step' => route('admin.webshop.translations.runs.step', $run),
            'cancel' => route('admin.webshop.translations.runs.cancel', $run),
            'restore' => route('admin.webshop.translations.runs.restore', $run),
            'report' => route('admin.webshop.translations.runs.show', $run),
        ];
    }

    public function showRun(Request $request, WebshopTranslationRun $run)
    {
        $items = $run->items()->orderBy('id')->get();

        $data = [
            'run' => $run,
            'items' => $items->map(fn ($item) => WebshopTranslationRunService::itemSummary($item)),
            'localeNames' => self::localeNames(),
            'modes' => WebshopTranslationRunService::MODES,
            'groups' => WebshopTranslationStatusService::GROUPS,
        ];

        return $request->ajax()
            ? response()->json(['html' => view('admin.webshop.translations.partials.report', $data)->render()])
            : view('admin.webshop.translations.run', $data);
    }

    public function cancelRun(WebshopTranslationRun $run)
    {
        WebshopTranslationRunService::cancel($run);

        return response()->json(['success' => true] + WebshopTranslationRunService::progress($run->fresh()));
    }

    public function restoreRun(WebshopTranslationRun $run)
    {
        $restored = WebshopTranslationRunService::restoreRun($run);

        return response()->json(['success' => true, 'message' => "Visszaállítva: {$restored} mező."] + WebshopTranslationRunService::progress($run->fresh()));
    }

    public function restoreItem(WebshopTranslationRunItem $item)
    {
        $restored = WebshopTranslationRunService::restoreItem($item);

        return response()->json(['success' => true, 'message' => "Visszaállítva: {$restored} mező.", 'item' => WebshopTranslationRunService::itemSummary($item->fresh())]);
    }

    // --------------------------------------------------------------------------------------------- segédek

    /** A nyelvek neve (a core Lang::LANG_NAMES-éből, hiányában a kód) */
    public static function localeNames(): array
    {
        $names = [];
        foreach (WebshopTranslationService::locales() as $locale) {
            $names[$locale] = (class_exists(\App\Helpers\Lang::class) && defined(\App\Helpers\Lang::class . '::LANG_NAMES'))
                ? (\App\Helpers\Lang::LANG_NAMES[$locale] ?? strtoupper($locale))
                : strtoupper($locale);
        }

        return $names;
    }

    /** Űrlap-mezőnév a pont-útvonalas mezőkhöz (a PHP a pontot aláhúzásra cserélné a kérésben) */
    public static function inputKey(string $field): string
    {
        return str_replace('.', '__', $field);
    }

    private function resolve(string $type, int $recordId, string $locale): array
    {
        abort_unless(in_array($type, WebshopTranslationRunService::RUN_TYPES, true), 404);
        abort_unless(in_array($locale, WebshopTranslationService::targetLocales(), true), 404);
        $record = WebshopTranslationRunService::findRecord($type, $recordId);
        abort_unless($record !== null, 404);

        return [$record, $locale];
    }

    /**
     * A kijelölés: típus + azonosítók (`ids[]`), vagy „a szűrésnek megfelelő összes” (`all=1` + a lista szűrői).
     * @return array<string, int[]>
     */
    private function selection(Request $request): array
    {
        $type = (string) $request->input('type', '');
        abort_unless(in_array($type, WebshopTranslationRunService::RUN_TYPES, true), 422);

        if ($type === 'setting') {
            return ['setting' => [0]];
        }

        if ($request->boolean('all')) {
            $filters = WebshopTranslationOverviewService::normalizeFilters((array) $request->input('filters', []) + ['type' => $type]);
            $rows = WebshopTranslationOverviewService::filterByState(
                WebshopTranslationOverviewService::rows($type, WebshopTranslationOverviewService::records($type, $filters), WebshopTranslationService::targetLocales()),
                $filters['locale'],
                $filters['state']
            );

            return [$type => array_column($rows, 'id')];
        }

        return [$type => array_values(array_unique(array_map('intval', (array) $request->input('ids', []))))];
    }

    private function selectionLabel(array $selection): string
    {
        $parts = [];
        foreach ($selection as $type => $ids) {
            $label = WebshopTranslationService::TYPES[$type]['label'] ?? $type;
            if ($type === 'setting') {
                $parts[] = 'Webshop-szövegek';
            } elseif (count($ids) === 1) {
                $record = WebshopTranslationRunService::findRecord($type, (int) $ids[0]);
                $parts[] = $label . ': ' . WebshopTranslationRunService::recordLabel($type, $record);
            } else {
                $parts[] = $label . ' — ' . count($ids) . ' tétel';
            }
        }

        return implode(', ', $parts);
    }
}
