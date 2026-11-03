{{-- A Fordítások lista sorai (az oldal és az AJAX-szűrés is ezt rendereli). $rows: WebshopTranslationOverviewService::rows() lapja. --}}
@php
    $wsTrStateLabels = ['done' => 'Kész', 'partial' => 'Részben kész', 'outdated' => 'Elavult', 'missing' => 'Hiányzik', 'empty' => 'Nincs mit fordítani'];
@endphp
@forelse($rows as $wsTrRow)
    <tr data-id="{{ $wsTrRow['id'] }}">
        <td>
            @if($aiAvailable)
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input js-tr-check" id="trCheck{{ $wsTrRow['id'] }}" value="{{ $wsTrRow['id'] }}">
                    <label class="custom-control-label" for="trCheck{{ $wsTrRow['id'] }}"><span class="sr-only">Kijelölés</span></label>
                </div>
            @endif
        </td>
        <td>
            <div class="d-flex align-items-center" style="gap: 10px">
                @if($wsTrRow['thumb'])
                    <img src="{{ $wsTrRow['thumb'] }}" alt="" class="ws-tr-thumb">
                @endif
                <div>
                    <span class="fw-600">{{ $wsTrRow['label'] }}</span>
                    @unless($wsTrRow['active'])
                        <span class="ws-chip ml-1">Inaktív</span>
                    @endunless
                    @if($wsTrRow['sub'])
                        <div class="text-muted fs-14">{{ $wsTrRow['sub'] }}</div>
                    @endif
                </div>
            </div>
        </td>
        @foreach($locales as $wsTrLocale)
            @php($wsTrSummary = $wsTrRow['locales'][$wsTrLocale])
            <td>
                <a href="{{ route('admin.webshop.translations.edit', [$wsTrRow['type'], $wsTrRow['id'], $wsTrLocale]) }}"
                   class="ws-tr-state is-{{ $wsTrSummary['overall'] }}"
                   title="{{ $wsTrStateLabels[$wsTrSummary['overall']] ?? '' }} — {{ $wsTrSummary['done'] }} / {{ $wsTrSummary['total'] }} mező kész{{ $wsTrSummary['outdated'] ? ', ' . $wsTrSummary['outdated'] . ' elavult' : '' }}{{ $wsTrSummary['manual'] ? ', ' . $wsTrSummary['manual'] . ' kézi fordítás' : '' }}. Szerkesztés">
                    {{ strtoupper($wsTrLocale) }} {{ $wsTrSummary['done'] }}/{{ $wsTrSummary['total'] }}
                </a>
                @if($wsTrSummary['manual'])
                    <i class="fa-solid fa-user-pen text-muted fs-14 ml-1" title="Kézi fordítású mező: {{ $wsTrSummary['manual'] }}"></i>
                @endif
            </td>
        @endforeach
        <td class="fs-14">
            @if($wsTrRow['last'])
                {{ \Illuminate\Support\Carbon::parse($wsTrRow['last']['at'])->format('Y.m.d. H:i') }}
                <div class="text-muted">{{ $wsTrRow['last']['origin'] === 'ai' ? 'AI' : 'kézi' }} · {{ strtoupper($wsTrRow['last']['locale']) }}</div>
            @else
                <span class="text-muted">—</span>
            @endif
        </td>
        <td class="text-right text-nowrap">
            @foreach($wsTrRow['site_urls'] as $wsTrLocale => $wsTrUrl)
                <a href="{{ $wsTrUrl }}" target="_blank" rel="noopener" class="btn btn-sm btn-light border" title="Megnyitás a weboldalon ({{ strtoupper($wsTrLocale) }})">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                </a>
            @endforeach
            @if($wsTrRow['edit_url'])
                <a href="{{ $wsTrRow['edit_url'] }}" class="btn btn-sm btn-light border" title="Az eredeti szerkesztése">
                    <i class="fa-solid fa-pen"></i>
                </a>
            @endif
            @if($aiAvailable)
                <button type="button" class="btn btn-sm btn-light border js-tr-translate-one" data-id="{{ $wsTrRow['id'] }}" title="Fordítás AI-val">
                    <i class="fa-solid fa-language"></i>
                </button>
            @endif
        </td>
    </tr>
@empty
    <tr>
        <td colspan="{{ 4 + count($locales) }}" class="text-center text-muted py-4">Nincs a szűrésnek megfelelő tétel.</td>
    </tr>
@endforelse
