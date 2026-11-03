{{-- Az AI-fordítás beállításai (WebshopTranslationController@dialog). A becslést a JS kéri le minden változáskor. --}}
<p class="mb-1"><b>{{ $selectionLabel }}</b></p>
<span class="d-block text-muted fs-14 lh-12 mb-3">
    A forrás az eredeti (alapnyelvi) szöveg. Az eredmény azonnal élesedik a weboldalon; minden felülírt fordításról mentés
    készül, így a futás vagy egy-egy tétele a riportból visszavonható. A futás lépésenként megy — szüneteltethető, és bezárt
    ablak vagy elfogyott AI-kredit után is folytatható.
</span>

<form class="js-tr-options" onsubmit="return false">
    <div class="row">
        <div class="col-md-4">
            <p class="font-weight-bold fs-14 mb-1">Nyelvek</p>
            @foreach($locales as $locale)
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="trOptLocale_{{ $locale }}" name="locales[]" value="{{ $locale }}"
                           @checked(!$preselectLocale || $preselectLocale === $locale)>
                    <label class="custom-control-label fs-14" for="trOptLocale_{{ $locale }}">{{ $localeNames[$locale] ?? strtoupper($locale) }}</label>
                </div>
            @endforeach
        </div>
        <div class="col-md-4 mt-3 mt-md-0">
            <p class="font-weight-bold fs-14 mb-1">Mely mezőket fordítsa?</p>
            @foreach($modes as $mode => $modeLabel)
                <div class="custom-control custom-radio">
                    <input type="radio" class="custom-control-input" id="trOptMode_{{ $mode }}" name="mode" value="{{ $mode }}" @checked($loop->first)>
                    <label class="custom-control-label fs-14" for="trOptMode_{{ $mode }}">{{ $modeLabel }}</label>
                </div>
            @endforeach
            <div class="custom-control custom-checkbox mt-2">
                <input type="checkbox" class="custom-control-input" id="trOptKeepManual" name="keep_manual" value="1" checked>
                <label class="custom-control-label fs-14" for="trOptKeepManual">A kézzel javított fordítást ne írja felül</label>
            </div>
        </div>
        <div class="col-md-4 mt-3 mt-md-0">
            <p class="font-weight-bold fs-14 mb-1">Tartalmak</p>
            @foreach($groups as $group => $groupLabel)
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="trOptGroup_{{ $group }}" name="groups[]" value="{{ $group }}" checked>
                    <label class="custom-control-label fs-14" for="trOptGroup_{{ $group }}">{{ $groupLabel }}</label>
                </div>
            @endforeach
        </div>
    </div>
</form>

<div class="ws-tr-estimate js-tr-estimate mt-3">
    <i class="fa-solid fa-spinner fa-spin"></i> Becslés…
</div>
