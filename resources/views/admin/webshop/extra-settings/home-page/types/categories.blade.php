{{--
    Kiválasztott kategóriák listája.

    A sorrend a rejtett inputok DOM-sorrendje, és az űrlappal együtt megy —
    nincs külön AJAX mentés, így nem tud elcsúszni a mentetlen szerkesztéstől.
--}}
<div class="form-group">
    <label>Megjelenő kategóriák</label>

    <div class="form-row align-items-end mb-2">
        <div class="col-md-8">
            <select class="form-control js-hb-cat-select" data-block="{{ $block->id }}">
                <option value="">— válassz kategóriát —</option>
                @foreach($categoryOptions as $catId => $catName)
                    <option value="{{ $catId }}">{{ $catName }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <button type="button" class="btn btn-outline-primary btn-block js-hb-cat-add" data-block="{{ $block->id }}">
                <i class="fa fa-plus mr-1"></i> Hozzáadás
            </button>
        </div>
    </div>

    <div class="js-hb-list" data-block="{{ $block->id }}">
        @foreach($items as $item)
            @php($hbMissing = is_array($item) && !empty($item['missing']))
            <div class="d-flex align-items-center border rounded p-2 mb-1 js-hb-item">
                <span class="js-hb-item-handle mr-2 text-muted" style="cursor: move;">
                    <i class="fa fa-grip-vertical"></i>
                </span>
                <span class="mr-auto @if($hbMissing) text-danger @endif">
                    @if($hbMissing)
                        Törölt kategória (#{{ $item['id'] }})
                    @else
                        {{ $item->name_singular }}
                    @endif
                </span>
                <input type="hidden" name="category_ids[]" value="{{ $hbMissing ? $item['id'] : $item->id }}">
                <button type="button" class="btn btn-sm btn-outline-danger js-hb-item-remove">
                    <i class="fa fa-times"></i>
                </button>
            </div>
        @endforeach
    </div>

    <span class="text-muted fs-14">
        A kategóriakártyához listakép szükséges — enélkül a kártya helyőrző ikont mutat.
    </span>
</div>
