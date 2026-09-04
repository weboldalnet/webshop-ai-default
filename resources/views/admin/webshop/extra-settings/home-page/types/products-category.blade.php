{{--
    Termékek egy kiválasztott kategóriából.

    Az üres első opció KÖTELEZŐ: enélkül, ha a korábban választott kategóriát
    időközben törlik, a böngésző a lista első elemét küldené vissza, és a blokk
    némán egy másik kategóriára állna át.
--}}
@php
    $hbCategoryId = $block->setting('category_id');
    $hbHianyzo = $hbCategoryId && !array_key_exists((int) $hbCategoryId, $categoryOptions);
@endphp

@if($hbHianyzo)
    <div class="alert alert-warning py-2">
        A korábban kiválasztott kategória már nem létezik. Válassz újat, különben a blokk üresen marad.
    </div>
@endif

<div class="form-row">
    <div class="col-md-6 form-group">
        <label for="hb-category-{{ $block->id }}">Kategória</label>
        <select class="form-control" id="hb-category-{{ $block->id }}" name="category_id">
            <option value="">— válassz —</option>
            @foreach($categoryOptions as $catId => $catName)
                <option value="{{ $catId }}" @if((int) $hbCategoryId === (int) $catId) selected @endif>{{ $catName }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-3 form-group">
        <label for="hb-limit-{{ $block->id }}">Megjelenő termékek száma</label>
        <input type="number" min="1" max="48" class="form-control" id="hb-limit-{{ $block->id }}"
               name="limit" value="{{ $block->setting('limit', 8) }}">
    </div>

    <div class="col-md-3 form-group">
        <label for="hb-sort-{{ $block->id }}">Rendezés</label>
        <select class="form-control" id="hb-sort-{{ $block->id }}" name="sort">
            @foreach($productSorts as $sortKey => $sortLabel)
                <option value="{{ $sortKey }}" @if($block->setting('sort', 'sort_order') === $sortKey) selected @endif>{{ $sortLabel }}</option>
            @endforeach
        </select>
    </div>
</div>
