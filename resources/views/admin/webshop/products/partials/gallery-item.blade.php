{{-- Galéria-elem: a szerkesztőben és a feltöltés AJAX-válaszában is ez renderelődik. A JS a
     #galleryItem{id} azonosítóra, a .js-sort-id mezőre és a gombok js- osztályaira köt. --}}
<div class="col-xl-3 col-lg-4 col-6 mb-3 ws-gallery-item" id="galleryItem{{ $img->id }}">
    <input type="hidden" class="js-sort-id" value="{{ $img->id }}">
    <div class="ws-gallery-card">
        <img src="{{ $img->image }}" class="img-fluid" alt="{{ $img->alt }}">
        <div class="ws-gallery-toolbar">
            <div class="ws-drag-handle" title="Húzd a sorrendhez"><i class="fa fa-grip-vertical"></i></div>
            <div class="custom-control custom-switch" title="Látható a termékoldalon">
                <input type="checkbox" class="custom-control-input js-toggle-active" id="gal{{ $img->id }}" data-id="{{ $img->id }}" data-url="{{ route('admin.webshop.products.gallery.toggle-active') }}" @if($img->is_active) checked @endif>
                <label class="custom-control-label" for="gal{{ $img->id }}"></label>
            </div>
            <div class="d-flex ml-auto">
                <button type="button" class="btn btn-sm btn-light js-edit-gallery-alt mr-1" title="Alt szöveg"
                        data-id="{{ $img->id }}"
                        data-alt="{{ $img->alt }}">
                    <i class="fa fa-info-circle"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger js-delete-gallery-item" title="Törlés"
                        data-id="{{ $img->id }}"
                        data-url="{{ route('admin.webshop.products.gallery.destroy', [$product, $img]) }}">
                    <i class="fa fa-trash-alt"></i>
                </button>
            </div>
        </div>
    </div>
</div>
