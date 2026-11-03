@extends('admin.layouts.layout')
@section('title', 'Fordítás — ' . $label)

@section('content')
    @php
        /*
         * FORDÍTÁS-SZERKESZTŐ (2026-11-03): egy tétel egy nyelven, mezőnként egymás mellett az eredeti és a fordítás.
         * A mentés kézi fordításként (origin = manual) tárol — az AI-futás alapból nem írja felül. Az üresre törölt
         * mező fordítása törlődik (a weboldalon az eredeti jelenik meg).
         */
        use Weboldalnet\WebshopAiDefault\Http\Controllers\Admin\Webshop\WebshopTranslationController as WsTrController;

        $wsTrStateLabels = ['done' => ['Naprakész', 'is-good'], 'outdated' => ['Elavult — az eredeti azóta változott', 'is-warn'], 'missing' => ['Nincs lefordítva', '']];
        $wsTrFieldLabels = [
            'name' => 'Név', 'secondary_name' => 'Másodlagos név', 'short_desc' => 'Rövid leírás', 'description' => 'Leírás',
            'slug' => 'URL-szó (slug)', 'og_title' => 'Megosztási cím', 'og_description' => 'Megosztási leírás',
            'name_singular' => 'Név (egyes szám)', 'name_plural' => 'Név (többes szám)', 'suffix' => 'Mértékegység',
            'alt' => 'Alt-szöveg', 'title' => 'Cím', 'content' => 'Tartalom', 'img_alt' => 'Kép alt-szövege',
        ];
        $wsTrFieldLabel = function (string $field) use ($wsTrFieldLabels, $type) {
            if (isset($wsTrFieldLabels[$field])) {
                return $wsTrFieldLabels[$field];
            }
            $parts = explode('.', $field);
            if ($parts[0] === 'legal_info' && count($parts) === 3) {
                return 'Jogi információ: ' . \Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopProductLegalInfoService::fieldLabel($parts[2]);
            }
            if ($type === 'setting') {
                return \Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopTranslationService::settingLabel($field);
            }

            return str_replace(['.', '_'], [' › ', ' '], $field);
        };
        $wsTrSummary = \Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopTranslationStatusService::summarize($fields);
    @endphp

    @include('admin.commons.tinymce-img-upload', ['tinyHeight' => 260])

    <div class="container-fluid ws-editor mt-3 mb-150" style="max-width: 1240px">
        @include('admin.webshop.partials.alerts')

        <div class="ws-editor-head">
            <div>
                <span class="ws-editor-kicker">Fordítás · {{ $typeLabel }}</span>
                <h2 class="ws-editor-title">{{ $label }}</h2>
                <div class="ws-chips">
                    <span class="ws-chip is-accent"><i class="fa-solid fa-language"></i> {{ $localeNames[$locale] ?? strtoupper($locale) }}</span>
                    <span class="ws-chip">{{ $wsTrSummary['done'] }} / {{ $wsTrSummary['total'] }} mező kész</span>
                    @if($wsTrSummary['outdated'])
                        <span class="ws-chip is-warn">{{ $wsTrSummary['outdated'] }} elavult</span>
                    @endif
                    @if($wsTrSummary['manual'])
                        <span class="ws-chip"><i class="fa-solid fa-user-pen"></i> {{ $wsTrSummary['manual'] }} kézi fordítás</span>
                    @endif
                </div>
            </div>
            <div class="ws-editor-head-actions">
                @if($aiAvailable)
                    <button type="button" class="btn btn-outline-primary js-tr-translate-record" data-type="{{ $type }}" data-id="{{ $recordId }}" data-locale="{{ $locale }}">
                        <i class="fa-solid fa-language"></i> Fordítás AI-val
                    </button>
                @endif
                @if($adminUrl)
                    <a href="{{ $adminUrl }}" class="btn btn-outline-secondary"><i class="fa-solid fa-pen"></i> Az eredeti szerkesztése</a>
                @endif
                <a href="{{ route('admin.webshop.translations.index', ['type' => $type, 'locale' => $locale]) }}" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-arrow-left"></i> Fordítások
                </a>
            </div>
        </div>

        @if(count($locales) > 1)
            <ul class="nav nav-tabs mb-3">
                @foreach($locales as $wsTrLocale)
                    <li class="nav-item">
                        <a class="nav-link @if($wsTrLocale === $locale) active @endif" href="{{ route('admin.webshop.translations.edit', [$type, $recordId, $wsTrLocale]) }}">
                            {{ $localeNames[$wsTrLocale] ?? strtoupper($wsTrLocale) }}
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif

        <form method="POST" action="{{ route('admin.webshop.translations.update', [$type, $recordId, $locale]) }}" class="js-tr-edit-form" enctype="multipart/form-data">
            @csrf
            <div class="ws-card">
                @forelse($fields as $field => $wsTrField)
                    @php
                        $wsTrKey = WsTrController::inputKey($field);
                        $wsTrKind = $kinds[explode('.', $field)[0]] ?? 'text';
                        $wsTrIsHtml = $wsTrKind === 'html';
                        // nyelvenkénti dokumentum (ÁSZF, adatvédelem): fájl vagy link, nem szövegfordítás
                        $wsTrIsFile = $wsTrKind === 'file';
                        $wsTrIsUrl = $wsTrKind === 'url';
                        $wsTrIsLong = in_array($wsTrKind, ['textarea', 'html', 'json'], true) || mb_strlen($wsTrField['source']) > 120;
                        $wsTrState = $wsTrStateLabels[$wsTrField['state']] ?? ['', ''];
                    @endphp
                    <div class="ws-tr-field">
                        <div class="ws-tr-field-head">
                            <label for="trField_{{ $wsTrKey }}" class="mb-0">{{ $wsTrFieldLabel($field) }}</label>
                            <span class="ws-chip {{ $wsTrState[1] }}">{{ $wsTrState[0] }}</span>
                            @if($wsTrField['origin'] && $wsTrField['state'] !== 'missing')
                                <span class="ws-chip">{{ $wsTrField['origin'] === 'ai' ? 'AI-fordítás' : 'kézi fordítás' }}</span>
                            @endif
                        </div>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="ws-tr-source @if($wsTrIsHtml) is-html @endif">
                                    @if($wsTrIsFile || $wsTrIsUrl)
                                        <a href="{{ $wsTrField['source'] }}" target="_blank" rel="noopener"><i class="fa-regular fa-file-lines"></i> {{ $wsTrIsFile ? basename($wsTrField['source']) : $wsTrField['source'] }}</a>
                                    @elseif($wsTrIsHtml)
                                        {!! strip_tags($wsTrField['source'], '<p><br><b><strong><i><em><u><ul><ol><li><h2><h3><h4><a><span><table><tr><td><th><thead><tbody>') !!}
                                    @else
                                        {!! nl2br(e($wsTrField['source'])) !!}
                                    @endif
                                </div>
                                <span class="ws-hint">Eredeti ({{ $localeNames[\Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopTranslationService::defaultLocale()] ?? '' }})</span>
                            </div>
                            <div class="col-lg-6 mt-2 mt-lg-0">
                                @if($wsTrIsFile)
                                    @if($wsTrField['value'])
                                        <div class="mb-2 fs-14"><a href="{{ $wsTrField['value'] }}" target="_blank" rel="noopener"><i class="fa-regular fa-file-lines"></i> {{ basename($wsTrField['value']) }}</a></div>
                                    @endif
                                    <input type="hidden" name="fields[{{ $wsTrKey }}]" value="{{ $wsTrField['value'] }}">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fa-regular fa-file-pdf fs-18"></i></span>
                                        </div>
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" id="trField_{{ $wsTrKey }}" name="files[{{ $wsTrKey }}]" accept=".pdf,.doc,.docx,.odt">
                                            <label class="custom-file-label text-muted" for="trField_{{ $wsTrKey }}">{{ $wsTrField['value'] ? 'Csere másik fájlra' : 'Fájl' }}</label>
                                        </div>
                                    </div>
                                @elseif($wsTrIsUrl)
                                    <input type="url" class="form-control" id="trField_{{ $wsTrKey }}" name="fields[{{ $wsTrKey }}]" value="{{ $wsTrField['value'] }}" placeholder="https://">
                                @elseif($wsTrIsLong)
                                    <textarea class="form-control @if($wsTrIsHtml) js-tinymce @endif" id="trField_{{ $wsTrKey }}" name="fields[{{ $wsTrKey }}]"
                                              rows="{{ $wsTrIsHtml ? 8 : 4 }}">{{ $wsTrField['value'] }}</textarea>
                                @else
                                    <input type="text" class="form-control" id="trField_{{ $wsTrKey }}" name="fields[{{ $wsTrKey }}]" value="{{ $wsTrField['value'] }}">
                                @endif
                                @if($wsTrIsFile)
                                    <span class="ws-hint">A dokumentum ezen a nyelven (PDF, DOC, DOCX, ODT, legfeljebb 10 MB). Az ezen a nyelven leadott rendelések visszaigazoló levele ezt csatolja; ha nincs, a weboldal az eredetit mutatja.</span>
                                @elseif($wsTrIsUrl)
                                    <span class="ws-hint">A dokumentum címe ezen a nyelven. Ha ez és a feltöltött fájl is üres, a weboldal az eredetit mutatja.</span>
                                @elseif($field === 'slug')
                                    <span class="ws-hint">Kisbetűs, ékezet nélküli URL-szó; mentéskor egységesítjük, és ha foglalt, sorszámot kap. Üresen a weboldal az eredeti címszót használja.</span>
                                @elseif($wsTrField['state'] === 'outdated')
                                    <div class="custom-control custom-checkbox mt-1">
                                        <input type="checkbox" class="custom-control-input" id="trConfirm_{{ $wsTrKey }}" name="confirm[]" value="{{ $wsTrKey }}">
                                        <label class="custom-control-label fs-14" for="trConfirm_{{ $wsTrKey }}">A fordítás így is naprakész (az eredeti változása nem érinti)</label>
                                    </div>
                                @else
                                    <span class="ws-hint">Üresen hagyva a weboldalon az eredeti szöveg jelenik meg.</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-muted mb-0">Ennél a tételnél nincs fordítható szöveg (az eredeti mezők üresek).</p>
                @endforelse
            </div>

            @if($fields)
                <div class="admin-save-btn-container">
                    <button type="submit" class="btn btn-primary fs-18 fw-600"><i class="fa fa-save"></i> Mentés</button>
                </div>
            @endif
        </form>
    </div>

    @include('admin.webshop.translations.partials.modal')

    <link rel="stylesheet" href="/packages/webshop/admin/css/webshop-admin.css">
    <script src="/packages/webshop/admin/js/webshop-translations.js?v={{ @filemtime(public_path('packages/webshop/admin/js/webshop-translations.js')) }}"></script>
    <script>
        WebshopTranslations.initRecordButtons({
            dialogUrl: @json(route('admin.webshop.translations.dialog')),
            estimateUrl: @json(route('admin.webshop.translations.estimate')),
            storeUrl: @json(route('admin.webshop.translations.runs.store')),
            reloadOnFinish: true
        });
    </script>
@endsection
