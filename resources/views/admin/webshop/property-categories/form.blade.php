@php
    /*
     * TULAJDONSÁGCSOPORT ŰRLAPJA (újratervezve 2026-10-28). A mezőnevek a régiek; a szerkesztő doboz
     * alatt a szűrőtípusok magyarázata, a kiválasztott típus kiemelve (a .js-filter-type változására).
     */
    $isEdit = isset($propertyCategory) && $propertyCategory;
    $wsType = old('filter_type', $isEdit ? $propertyCategory->filter_type : 'checkbox');
    $wsTypes = [
        'checkbox' => [
            'label' => 'Több választás (checkbox)',
            'icon' => 'fa-square-check',
            'product' => 'A terméknél több érték is bejelölhető.',
            'shopper' => 'A szűrőben a vásárló több értéket is bepipálhat egyszerre.',
            'example' => 'Pl. Illat: Levendula és Citrom; Felhasználás: Színes és Fehér ruhákhoz.',
        ],
        'radio' => [
            'label' => 'Egy választás (radio)',
            'icon' => 'fa-circle-dot',
            'product' => 'A terméknél pontosan egy érték választható.',
            'shopper' => 'A szűrőben egyszerre egy érték választható ki.',
            'example' => 'Pl. Forma: Gél, Por vagy Kapszula — egy termék csak az egyik.',
        ],
        'number' => [
            'label' => 'Szám (number)',
            'icon' => 'fa-hashtag',
            'product' => 'Nincsenek előre felvett értékek: a terméknél egy számot adsz meg, a mértékegységgel.',
            'shopper' => 'A szűrőben tól–ig tartomány adható meg (min. és max.).',
            'example' => 'Pl. Adagok száma: 30 adag; Tömeg: 1,5 kg.',
        ],
    ];
@endphp

<div class="ws-card">
    <header class="ws-card-head">
        <span class="ws-card-icon"><i class="fa-solid fa-sliders"></i></span>
        <div class="ws-card-heading"><h3 class="ws-card-title">Alapadatok</h3></div>
    </header>

    <div class="form-group">
        <label for="name">Név <span class="ws-req">*</span></label>
        <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $isEdit ? $propertyCategory->name : '') }}" required>
        <span class="ws-hint">Pl. Illat, Kiszerelés — ez a csoport címe a szűrőben és a termékoldalon.</span>
    </div>

    <div class="form-row">
        <div class="form-group col-md-7">
            <label for="filter_type">Szűrőtípus <span class="ws-req">*</span></label>
            <select class="form-control js-filter-type" id="filter_type" name="filter_type" required>
                @foreach($wsTypes as $wsTypeKey => $wsTypeDef)
                    <option value="{{ $wsTypeKey }}" @if($wsType === $wsTypeKey) selected @endif>{{ $wsTypeDef['label'] }}</option>
                @endforeach
            </select>
            <span class="ws-hint">Mit jelent az egyes típus? Lásd lent.</span>
        </div>
        <div class="form-group col-md-5 js-suffix-group" style="{{ $wsType !== 'number' ? 'display:none' : '' }}">
            <label for="suffix">Mértékegység</label>
            <input type="text" class="form-control" id="suffix" name="suffix" value="{{ old('suffix', $isEdit ? $propertyCategory->suffix : '') }}" placeholder="pl. kg, cm, db">
            <span class="ws-hint">A szám után jelenik meg, pl. 30 adag.</span>
        </div>
    </div>

    @if($isEdit && $propertyCategory->filter_type !== 'number' && $propertyCategory->properties()->exists())
        <span class="ws-hint mt-0 mb-3 js-type-change-note" style="display:none">
            <i class="fa-solid fa-triangle-exclamation text-warning"></i>
            Szám típusra váltva a felvett értékek ({{ $propertyCategory->properties()->count() }} db) nem jelennek meg többé a termékeknél és a szűrőben.
        </span>
    @endif

    <div class="custom-control custom-switch">
        <input type="checkbox" class="custom-control-input" id="filter_enabled" name="filter_enabled" value="1"
               @if(old('filter_enabled', $isEdit ? $propertyCategory->filter_enabled : false)) checked @endif>
        <label class="custom-control-label" for="filter_enabled">Szűrőként megjelenik</label>
    </div>
    <span class="ws-hint">
        A kategóriaoldal szűrősávjában látszik — ha a csoport hozzá van rendelve a kategóriához, és van olyan termék,
        amelynél ki van töltve.
    </span>
</div>

{{-- A szűrőtípusok magyarázata (a kiválasztott kiemelve) --}}
<div class="ws-card">
    <header class="ws-card-head">
        <span class="ws-card-icon"><i class="fa-solid fa-circle-question"></i></span>
        <div class="ws-card-heading">
            <h3 class="ws-card-title">Melyik szűrőtípust válaszd?</h3>
            <span class="ws-card-lead">A típus dönti el, hány érték adható meg egy terméknél, és hogyan szűrhet a vásárló.</span>
        </div>
    </header>
    <div class="ws-type-list">
        @foreach($wsTypes as $wsTypeKey => $wsTypeDef)
            <div class="ws-type-item js-type-item @if($wsType === $wsTypeKey) is-active @endif" data-type="{{ $wsTypeKey }}">
                <span class="ws-type-icon"><i class="fa-solid {{ $wsTypeDef['icon'] }}"></i></span>
                <div>
                    <div class="ws-type-name">{{ $wsTypeDef['label'] }}</div>
                    <div class="ws-type-row"><strong>Terméknél:</strong> {{ $wsTypeDef['product'] }}</div>
                    <div class="ws-type-row"><strong>Szűrőben:</strong> {{ $wsTypeDef['shopper'] }}</div>
                    <div class="ws-type-example">{{ $wsTypeDef['example'] }}</div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<script>
    // a kiválasztott szűrőtípus kiemelése a magyarázatban, és figyelmeztetés a szám típusra váltáskor
    $(document).on('change', '.js-filter-type', function () {
        var type = $(this).val();
        $('.js-type-item').each(function () {
            $(this).toggleClass('is-active', $(this).data('type') === type);
        });
        $('.js-type-change-note').toggle(type === 'number');
    });
</script>
