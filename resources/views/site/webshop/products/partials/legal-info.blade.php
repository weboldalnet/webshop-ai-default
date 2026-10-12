{{--
    Kötelező termékinformációk (Termékinformációk fül) – WebshopProductLegalInfoService.
    Csak a boltban bekapcsolt profilok kitöltött mezői jelennek meg.
--}}
@php
    $wsLegalService = \Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopProductLegalInfoService::class;
    $wsLegalSections = $wsLegalService::sectionsFor($product);
@endphp

<div class="ws-product-legal-info">
    @foreach($wsLegalSections as $wsLegalKey => $wsLegalSection)
        @if(count($wsLegalSections) > 1)
            <h3 class="fs-18 font-weight-bold mb-2 @if(!$loop->first) mt-4 @endif">{{ $wsLegalSection['label'] }}</h3>
        @endif
        <table class="table table-sm ws-legal-table mb-0">
            <tbody>
                @foreach($wsLegalSection['rows'] as $wsLegalRow)
                    <tr>
                        <th scope="row" class="font-weight-bold align-top" style="width: 35%;">{{ $wsLegalRow['label'] }}</th>
                        <td class="align-top">
                            @switch($wsLegalRow['type'])
                                @case('pictograms')
                                    <div class="d-flex flex-wrap">
                                        @foreach($wsLegalRow['value'] as $wsPictogram)
                                            <span class="d-inline-flex align-items-center mr-3 mb-2">
                                                {{-- Egyszerűsített GHS-jelzés: piros keretes rombusz a kóddal --}}
                                                <svg width="42" height="42" viewBox="0 0 34 34" aria-hidden="true" class="mr-2 flex-shrink-0">
                                                    <rect x="6" y="6" width="22" height="22" transform="rotate(45 17 17)" fill="#fff" stroke="#d00000" stroke-width="3"/>
                                                    <text x="17" y="20.5" text-anchor="middle" font-size="9" font-weight="700" fill="#000">{{ substr($wsPictogram, 3) }}</text>
                                                </svg>
                                                <span>{{ $wsPictogram }} – {{ $wsLegalService::PICTOGRAMS[$wsPictogram] ?? '' }}</span>
                                            </span>
                                        @endforeach
                                    </div>
                                    @break
                                @case('nutrition')
                                    <table class="table table-sm table-bordered mb-0 ws-nutrition-table" style="max-width: 420px;">
                                        <tbody>
                                            @foreach($wsLegalService::NUTRITION as $wsNutritionKey => $wsNutritionLabel)
                                                @if(isset($wsLegalRow['value'][$wsNutritionKey]))
                                                    <tr>
                                                        <td>{{ $wsNutritionLabel }}</td>
                                                        <td class="text-right ws-nowrap">{{ $wsLegalRow['value'][$wsNutritionKey] }}</td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                    @break
                                @case('url')
                                    <a href="{{ $wsLegalRow['value'] }}" target="_blank" rel="noopener">Megnyitás <i class="fa fa-external-link-alt ml-1"></i></a>
                                    @break
                                @default
                                    {{ $wsLegalService::formatText((string) $wsLegalRow['value']) }}
                            @endswitch
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach
</div>
