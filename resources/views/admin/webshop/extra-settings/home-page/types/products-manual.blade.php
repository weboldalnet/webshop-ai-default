{{--
    Egyenként kiválasztott termékek.

    A keresés a meglévő admin.webshop.products.search végpontot használja.
    A saját, blokkra szűkített pickert azért írjuk meg itt, mert a
    WebshopAdmin.initProductRelationPicker fixen a js-related-* osztályokra és a
    related_product_ids[] névre van drótozva – újrahasználva ütközne.
--}}
<div class="form-group">
    <label for="hb-prod-search-{{ $block->id }}">Megjelenő termékek</label>

    <div class="position-relative mb-2">
        <input type="text" class="form-control js-hb-prod-search"
               id="hb-prod-search-{{ $block->id }}"
               data-block="{{ $block->id }}"
               data-url="{{ route('admin.webshop.products.search') }}"
               placeholder="Kezdj gépelni a termék nevéből vagy cikkszámából…"
               autocomplete="off">
        <div class="list-group position-absolute w-100 js-hb-prod-results d-none"
             data-block="{{ $block->id }}" style="z-index: 20; max-height: 320px; overflow-y: auto;"></div>
    </div>

    <div class="js-hb-list" data-block="{{ $block->id }}">
        @foreach($items as $item)
            @php($hbMissing = is_array($item) && !empty($item['missing']))
            <div class="d-flex align-items-center border rounded p-2 mb-1 js-hb-item">
                <span class="js-hb-item-handle mr-2 text-muted" style="cursor: move;">
                    <i class="fa fa-grip-vertical"></i>
                </span>

                @if(!$hbMissing && ($item->primary_image_thumb ?? $item->primary_image))
                    <img src="{{ $item->primary_image_thumb ?? $item->primary_image }}" alt=""
                         class="mr-2 rounded border" style="width: 40px; height: 40px; object-fit: cover;">
                @endif

                <span class="mr-auto @if($hbMissing) text-danger @endif">
                    @if($hbMissing)
                        Törölt termék (#{{ $item['id'] }})
                    @else
                        {{ $item->name }}
                        @if($item->sku)
                            <span class="text-muted fs-14">({{ $item->sku }})</span>
                        @endif
                    @endif
                </span>

                <input type="hidden" name="product_ids[]" value="{{ $hbMissing ? $item['id'] : $item->id }}">
                <button type="button" class="btn btn-sm btn-outline-danger js-hb-item-remove">
                    <i class="fa fa-times"></i>
                </button>
            </div>
        @endforeach
    </div>

    <span class="text-muted fs-14">A sorrend húzással állítható, és a blokk mentésekor rögzül.</span>
</div>
