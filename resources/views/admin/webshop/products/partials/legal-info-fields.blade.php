{{--
    Kötelező termékinformációk szerkesztése (WebshopProductLegalInfoService).
    Csak a boltban bekapcsolt profilok mezői jelennek meg (Extra beállítások → Termékinformációk).
    A mezők neve: legal_info[profil][mező].
--}}
@php
    $wsLegalService = \Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopProductLegalInfoService::class;
    $wsLegalProfiles = $wsLegalService::enabledProfiles();
    $wsLegalData = old('legal_info', is_array($product->legal_info) ? $product->legal_info : []);
    $wsLegalDefaults = $wsLegalService::shopDefaults();
@endphp

<div class="ws-card" id="ws-sec-legal">
    <header class="ws-card-head">
        <span class="ws-card-icon"><i class="fa-solid fa-clipboard-list"></i></span>
        <div class="ws-card-heading">
            <h3 class="ws-card-title">Kötelező termékinformációk</h3>
            <span class="ws-card-lead">
                @if(empty($wsLegalProfiles))
                    Nincs bekapcsolva termékinformációs profil. A termék fajtájához tartozó kötelező adatokat
                    (gyártó, összetevők, veszélyjelek …) a
                    <a href="{{ route('admin.webshop.extra-settings.product-info.index') }}">Termékinformációk</a> beállításnál kapcsolhatod be.
                @else
                    A termékoldal „Termékinformációk” fülén jelennek meg, csak a kitöltött mezők. Bolt szintű alapértékek és
                    profilok: <a href="{{ route('admin.webshop.extra-settings.product-info.index') }}">Termékinformációk</a> beállítás.
                @endif
            </span>
        </div>
    </header>
        @unless(empty($wsLegalProfiles))
            @foreach($wsLegalProfiles as $wsProfile)
                @php($wsProfileDef = $wsLegalService::PROFILES[$wsProfile])
                <h4 class="ws-card-sub @if($loop->first) mt-0 @endif">{{ $wsProfileDef['label'] }}</h4>
                <div class="row">
                    @foreach($wsProfileDef['fields'] as $wsField => $wsConfig)
                        @php($wsName = 'legal_info[' . $wsProfile . '][' . $wsField . ']')
                        @php($wsValue = $wsLegalData[$wsProfile][$wsField] ?? null)
                        @php($wsId = 'legal_' . $wsProfile . '_' . $wsField)
                        <div class="form-group {{ in_array($wsConfig['type'], ['textarea', 'nutrition', 'pictograms']) ? 'col-12' : 'col-md-6' }}">
                            <label for="{{ $wsId }}">{{ $wsConfig['label'] }}</label>

                            @switch($wsConfig['type'])
                                @case('textarea')
                                    <textarea class="form-control" id="{{ $wsId }}" name="{{ $wsName }}" rows="3"
                                              @if(isset($wsLegalDefaults[$wsProfile][$wsField])) placeholder="Bolt szintű alapérték: {{ $wsLegalDefaults[$wsProfile][$wsField] }}" @endif>{{ is_string($wsValue) ? $wsValue : '' }}</textarea>
                                    @break
                                @case('select')
                                    <select class="form-control" id="{{ $wsId }}" name="{{ $wsName }}">
                                        @foreach($wsConfig['options'] as $wsOptValue => $wsOptLabel)
                                            <option value="{{ $wsOptValue }}" @if((string) $wsValue === (string) $wsOptValue) selected @endif>{{ $wsOptLabel }}</option>
                                        @endforeach
                                    </select>
                                    @break
                                @case('pictograms')
                                    <div class="d-flex flex-wrap" id="{{ $wsId }}">
                                        @foreach($wsLegalService::PICTOGRAMS as $wsCode => $wsPictoLabel)
                                            <div class="custom-control custom-checkbox mr-4 mb-1">
                                                <input type="checkbox" class="custom-control-input" id="{{ $wsId }}_{{ $wsCode }}"
                                                       name="{{ $wsName }}[]" value="{{ $wsCode }}"
                                                       @if(is_array($wsValue) && in_array($wsCode, $wsValue, true)) checked @endif>
                                                <label class="custom-control-label" for="{{ $wsId }}_{{ $wsCode }}">{{ $wsCode }} – {{ $wsPictoLabel }}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                    @break
                                @case('nutrition')
                                    <div class="form-row" id="{{ $wsId }}">
                                        @foreach($wsLegalService::NUTRITION as $wsNutKey => $wsNutLabel)
                                            <div class="col-md-3 col-6 mb-2">
                                                <label class="fs-14 mb-1" for="{{ $wsId }}_{{ $wsNutKey }}">{{ $wsNutLabel }}</label>
                                                <input type="text" class="form-control form-control-sm" id="{{ $wsId }}_{{ $wsNutKey }}"
                                                       name="{{ $wsName }}[{{ $wsNutKey }}]"
                                                       value="{{ is_array($wsValue) ? ($wsValue[$wsNutKey] ?? '') : '' }}"
                                                       placeholder="{{ $wsNutKey === 'energy' ? 'pl. 1046 kJ / 250 kcal' : 'pl. 3,2 g' }}">
                                            </div>
                                        @endforeach
                                    </div>
                                    @break
                                @default
                                    <input type="{{ $wsConfig['type'] === 'url' ? 'url' : 'text' }}" class="form-control" id="{{ $wsId }}" name="{{ $wsName }}"
                                           value="{{ is_string($wsValue) ? $wsValue : '' }}">
                            @endswitch

                            @if(!empty($wsConfig['hint']))
                                <span class="text-muted fs-14">{{ $wsConfig['hint'] }}</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endforeach

            @if(in_array('food', $wsLegalProfiles, true))
                <span class="ws-hint">A nettó mennyiség az „Ár és kiszerelés” szakasz Kiszerelés mezőjéből kerül a termékoldalra.</span>
            @endif
        @endunless
</div>
