<div class="col-md-3 col-6 mb-2 ws-gallery-item" id="galleryItem{{ $img->id }}">
    <input type="hidden" class="js-sort-id" value="{{ $img->id }}">
    <img src="{{ $img->image }}" class="img-fluid rounded mb-1">
    <div class="d-flex justify-content-between align-items-center">
        <div class="ws-drag-handle mr-1"><i class="fa fa-grip-vertical"></i></div>
        <div class="custom-control custom-switch mr-1">
            <input type="checkbox" class="custom-control-input js-toggle-active" id="gal{{ $img->id }}" data-id="{{ $img->id }}" data-url="{{ route('admin.webshop.products.gallery.toggle-active') }}" @if($img->is_active) checked @endif>
            <label class="custom-control-label" for="gal{{ $img->id }}"></label>
        </div>
        <button type="button" class="btn btn-sm btn-info js-edit-gallery-alt mr-1" 
                data-id="{{ $img->id }}" 
                data-alt="{{ $img->alt }}">
            <i class="fa fa-info-circle"></i>
        </button>
        <button type="button" class="btn btn-sm btn-danger js-delete-gallery-item" 
                data-id="{{ $img->id }}" 
                data-url="{{ route('admin.webshop.products.gallery.destroy', [$product, $img]) }}">
            <i class="fa fa-trash-alt"></i>
        </button>
    </div>
</div>
