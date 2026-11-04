{{--
    Fordítási sáv a webshop-szerkesztők tetejére (termék, kategória, tulajdonságcsoport, címke …), csak többnyelvű projektben:
    nyelvenként az állapot (link a fordítás-szerkesztőbe) és az AI-fordítás gombja.
    Bekötés: @include('admin.webshop.translations.partials.record-bar', ['type' => 'product', 'record' => $product])

    A webshop adatai minden nyelven az alapnyelvi adatbázisban vannak: ha az admin nyelvválasztója más nyelvre áll, ez az
    űrlap akkor is az EREDETI szöveget szerkeszti — erre a sáv figyelmeztet (a fordítás külön, a Fordítások oldalon megy).
--}}
@php
    $wsTrService = \Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopTranslationService::class;
    $wsTrShow = isset($record) && $record && $record->exists && $wsTrService::isMultiLanguage() && $wsTrService::targetLocales();
@endphp
@if($wsTrShow)
    @php
        $wsTrLocales = $wsTrService::targetLocales();
        $wsTrNames = \Weboldalnet\WebshopAiDefault\Http\Controllers\Admin\Webshop\WebshopTranslationController::localeNames();
        $wsTrRow = \Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopTranslationOverviewService::rows($type, collect([$record]), $wsTrLocales)[0] ?? null;
        $wsTrAi = \Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopTranslationRunService::isAvailable();
        $wsTrAdminLocale = app()->getLocale();
    @endphp
    @if($wsTrAdminLocale !== $wsTrService::defaultLocale())
        <div class="alert alert-warning d-flex align-items-start fs-14" style="gap: 10px">
            <i class="fa-solid fa-triangle-exclamation mt-1"></i>
            <div>
                Az admin most <b>{{ $wsTrNames[$wsTrAdminLocale] ?? strtoupper($wsTrAdminLocale) }}</b> nyelvre van állítva, de ez az űrlap a
                webshop <b>eredeti ({{ $wsTrNames[$wsTrService::defaultLocale()] ?? '' }})</b> adatait szerkeszti — a közös adatok (ár, készlet,
                kategória …) minden nyelvre érvényesek. A szövegek fordítását a lenti „Fordítás” linkeken szerkesztheted.
            </div>
        </div>
    @endif
    @if($wsTrRow)
        <div class="ws-tr-record-bar {{ $wsTrAi ? '' : 'mb-3' }}">
            <span class="fw-600"><i class="fa-solid fa-language"></i> Fordítások:</span>
            @foreach($wsTrLocales as $wsTrLocale)
                @php($wsTrSummary = $wsTrRow['locales'][$wsTrLocale])
                <a href="{{ route('admin.webshop.translations.edit', [$type, $record->getKey(), $wsTrLocale]) }}" class="ws-tr-state is-{{ $wsTrSummary['overall'] }}"
                   title="{{ $wsTrNames[$wsTrLocale] ?? strtoupper($wsTrLocale) }}: {{ $wsTrSummary['done'] }} / {{ $wsTrSummary['total'] }} mező kész — szerkesztés">
                    {{ strtoupper($wsTrLocale) }} {{ $wsTrSummary['done'] }}/{{ $wsTrSummary['total'] }}
                </a>
            @endforeach
            @if($wsTrAi)
                <button type="button" class="btn btn-sm btn-light border ml-auto js-tr-translate-record" data-type="{{ $type }}" data-id="{{ $record->getKey() }}">
                    <i class="fa-solid fa-language"></i> Fordítás AI-val
                </button>
            @endif
        </div>

        @if($wsTrAi)
            <span class="ws-hint d-block mb-3">Az AI a mentett eredeti szöveget fordítja — előbb mentsd a módosításokat.</span>
            @once
                @include('admin.webshop.translations.partials.modal')
                <script src="/packages/webshop/admin/js/webshop-translations.js?v={{ @filemtime(public_path('packages/webshop/admin/js/webshop-translations.js')) }}"></script>
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        WebshopTranslations.initRecordButtons({
                            dialogUrl: @json(route('admin.webshop.translations.dialog')),
                            estimateUrl: @json(route('admin.webshop.translations.estimate')),
                            storeUrl: @json(route('admin.webshop.translations.runs.store')),
                            reloadOnFinish: true
                        });
                    });
                </script>
            @endonce
        @endif
    @endif
@endif
