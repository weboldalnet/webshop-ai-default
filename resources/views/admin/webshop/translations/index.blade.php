@extends('admin.layouts.layout')
@section('title', 'Fordítások')

@section('content')
    @php
        /*
         * WEBSHOP › FORDÍTÁSOK (2026-11-03): mi van lefordítva a többi nyelvre — nyelvenkénti kártyák, szűrhető lista
         * (AJAX: a sorokat a partials/rows adja), kézi szerkesztés (edit), tömeges AI-fordítás kijelölésre vagy a
         * szűrésnek megfelelő összes tételre (webshop-translations.js, a futás állapota a DB-ben).
         */
    @endphp

    <div class="container-fluid ws-editor mt-3 mb-150" style="max-width: 1240px">
        @include('admin.webshop.partials.alerts')

        <div class="ws-editor-head">
            <div>
                <span class="ws-editor-kicker">Webshop</span>
                <h2 class="ws-editor-title">Fordítások</h2>
                <div class="ws-chips">
                    <span class="ws-chip"><i class="fa-solid fa-language"></i> Alapnyelv: {{ $localeNames[\Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopTranslationService::defaultLocale()] ?? '' }}</span>
                    @if($hidesUntranslated)
                        <span class="ws-chip is-warn"><i class="fa-solid fa-eye-slash"></i> Fordítatlan termék a nyelvi oldalon: rejtve</span>
                    @else
                        <span class="ws-chip"><i class="fa-solid fa-eye"></i> Fordítatlan termék a nyelvi oldalon: az eredeti szöveggel látszik</span>
                    @endif
                    @unless($aiAvailable)
                        <span class="ws-chip"><i class="fa-solid fa-robot"></i> AI-fordítás nem elérhető</span>
                    @endunless
                </div>
            </div>
            <div class="ws-editor-head-actions">
                <a href="{{ route('admin.webshop.extra-settings.index') }}" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-gear"></i> Webshop beállítások
                </a>
            </div>
        </div>

        {{-- Nyelvenkénti állapot (minden típus, mezőszám szerint) --}}
        <div class="ws-tr-kpis">
            @foreach($locales as $locale)
                @php($wsKpi = $kpis[$locale] ?? ['total' => 0, 'done' => 0, 'missing' => 0, 'outdated' => 0, 'manual' => 0, 'percent' => 0])
                <div class="ws-card ws-tr-kpi">
                    <div class="d-flex align-items-baseline justify-content-between">
                        <span class="fw-700">{{ $localeNames[$locale] ?? strtoupper($locale) }}</span>
                        <span class="ws-tr-kpi-percent">{{ $wsKpi['percent'] }}%</span>
                    </div>
                    <div class="progress ws-tr-progress my-2" role="progressbar" aria-valuenow="{{ $wsKpi['percent'] }}" aria-valuemin="0" aria-valuemax="100">
                        <div class="progress-bar" style="width: {{ $wsKpi['percent'] }}%"></div>
                    </div>
                    <div class="ws-tr-kpi-counts">
                        <a href="#" class="js-tr-state-link" data-locale="{{ $locale }}" data-state="missing"><b>{{ $wsKpi['missing'] }}</b> hiányzó</a>
                        <a href="#" class="js-tr-state-link" data-locale="{{ $locale }}" data-state="outdated"><b>{{ $wsKpi['outdated'] }}</b> elavult</a>
                        <a href="#" class="js-tr-state-link" data-locale="{{ $locale }}" data-state="manual"><b>{{ $wsKpi['manual'] }}</b> kézi fordítás</a>
                        <span class="text-muted">{{ $wsKpi['done'] }} / {{ $wsKpi['total'] }} mező kész</span>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="ws-card">
            <form class="ws-tr-filters js-tr-filters" method="GET" action="{{ route('admin.webshop.translations.index') }}">
                <div class="form-group">
                    <label for="trType">Tartalom</label>
                    <select class="form-control" id="trType" name="type">
                        @foreach($types as $typeKey => $typeLabel)
                            <option value="{{ $typeKey }}" @selected($filters['type'] === $typeKey)>{{ $typeLabel }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="trLocale">Nyelv</label>
                    <select class="form-control" id="trLocale" name="locale">
                        @foreach($locales as $locale)
                            <option value="{{ $locale }}" @selected($filters['locale'] === $locale)>{{ $localeNames[$locale] ?? strtoupper($locale) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="trState">Állapot</label>
                    <select class="form-control" id="trState" name="state">
                        <option value="">Mind</option>
                        @foreach($states as $stateKey => $stateLabel)
                            <option value="{{ $stateKey }}" @selected($filters['state'] === $stateKey)>{{ $stateLabel }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group js-tr-category-filter" @if($filters['type'] !== 'product') style="display:none" @endif>
                    <label for="trCategory">Kategória</label>
                    <select class="form-control" id="trCategory" name="category_id">
                        <option value="">Mind</option>
                        @foreach($categories as $wsCategory)
                            <option value="{{ $wsCategory->id }}" @selected($filters['category_id'] === $wsCategory->id)>{{ $wsCategory->parent_id ? '— ' : '' }}{{ $wsCategory->name_singular }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="trActive">Aktív</label>
                    <select class="form-control" id="trActive" name="active">
                        <option value="">Mind</option>
                        <option value="1" @selected($filters['active'] === '1')>Csak aktív</option>
                        <option value="0" @selected($filters['active'] === '0')>Csak inaktív</option>
                    </select>
                </div>
                <div class="form-group ws-tr-filter-search">
                    <label for="trSearch">Keresés</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                        </div>
                        <input type="search" class="form-control" id="trSearch" name="q" value="{{ $filters['q'] }}" placeholder="Név, cikkszám…" autocomplete="off">
                    </div>
                </div>
            </form>

            <div class="ws-list-toolbar ws-tr-bulk">
                <span class="ws-hint mt-0"><b class="js-tr-total">{{ $total }}</b> tétel</span>
                @if($aiAvailable)
                    <span class="ws-hint mt-0 js-tr-selected-info" style="display:none">· kijelölve: <b class="js-tr-selected-count">0</b></span>
                    <div class="ml-auto d-flex flex-wrap" style="gap: 8px">
                        <button type="button" class="btn btn-outline-primary js-tr-translate-selected" disabled>
                            <i class="fa-solid fa-language"></i> Kijelöltek fordítása AI-val
                        </button>
                        <button type="button" class="btn btn-primary js-tr-translate-all" @disabled(!$total)>
                            <i class="fa-solid fa-language"></i> A szűrésnek megfelelő összes fordítása
                        </button>
                    </div>
                @endif
            </div>

            <div class="table-responsive">
                <table class="table table-hover ws-list-table ws-tr-table">
                    <thead>
                    <tr>
                        <th style="width:36px">
                            @if($aiAvailable)
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input js-tr-check-all" id="trCheckAll">
                                    <label class="custom-control-label" for="trCheckAll"><span class="sr-only">Mind</span></label>
                                </div>
                            @endif
                        </th>
                        <th>Tétel</th>
                        @foreach($locales as $locale)
                            <th style="width:120px">{{ $localeNames[$locale] ?? strtoupper($locale) }}</th>
                        @endforeach
                        <th style="width:150px">Utolsó fordítás</th>
                        <th style="width:130px"></th>
                    </tr>
                    </thead>
                    <tbody class="js-tr-rows">
                        @include('admin.webshop.translations.partials.rows')
                    </tbody>
                </table>
            </div>
            <div class="js-tr-pagination">{{ $rows->links() }}</div>
            <span class="ws-hint">
                Az állapot mezőszám szerint: <b>kész</b> — minden mező lefordítva és naprakész; <b>elavult</b> — az eredeti szöveg
                a fordítás óta változott; <b>kézi fordítás</b> — kézzel írt vagy átírt fordítás (az AI alapból nem írja felül).
                Az URL-szó (slug) a lefordított névből képződik, és a név későbbi változásától nem avul el.
            </span>
        </div>

        @if($runs->isNotEmpty())
            <div class="ws-card">
                <div class="ws-card-head">
                    <div class="ws-card-heading">
                        <h3 class="ws-card-title">Legutóbbi AI-fordítások</h3>
                        <p class="ws-card-lead">Az eredmény azonnal élesedik; a futás vagy egy-egy tétele a riportból visszavonható.</p>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table ws-list-table mb-0">
                        <thead>
                        <tr>
                            <th style="width:150px">Indítva</th>
                            <th>Kijelölés</th>
                            <th style="width:130px">Állapot</th>
                            <th style="width:160px">Tételek</th>
                            <th style="width:90px">Mezők</th>
                            <th style="width:90px"></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($runs as $wsRun)
                            <tr>
                                <td>{{ $wsRun->created_at?->format('Y.m.d. H:i') }}</td>
                                <td>{{ $wsRun->scope_label }} <span class="text-muted fs-14">· {{ implode(', ', array_map(fn ($l) => strtoupper($l), $wsRun->options['locales'] ?? [])) }}</span></td>
                                <td>@include('admin.webshop.translations.partials.run-status', ['status' => $wsRun->status])</td>
                                <td>{{ $wsRun->done_items }} / {{ $wsRun->total_items }}@if($wsRun->error_items) <span class="text-danger">· {{ $wsRun->error_items }} hiba</span>@endif</td>
                                <td>{{ $wsRun->translated_fields }}</td>
                                <td class="text-right">
                                    <button type="button" class="btn btn-sm btn-light border js-tr-open-report" data-url="{{ route('admin.webshop.translations.runs.show', $wsRun) }}">
                                        Riport
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

    @include('admin.webshop.translations.partials.modal')

    <link rel="stylesheet" href="/packages/webshop/admin/css/webshop-admin.css">
    <script src="/packages/webshop/admin/js/webshop-translations.js?v={{ @filemtime(public_path('packages/webshop/admin/js/webshop-translations.js')) }}"></script>
    <script>
        WebshopTranslations.initOverview({
            listUrl: @json(route('admin.webshop.translations.index')),
            dialogUrl: @json(route('admin.webshop.translations.dialog')),
            estimateUrl: @json(route('admin.webshop.translations.estimate')),
            storeUrl: @json(route('admin.webshop.translations.runs.store')),
            type: @json($filters['type'])
        });
    </script>
@endsection
