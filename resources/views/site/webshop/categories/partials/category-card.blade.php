@php
    $image = $cat->getListImageUrl();
@endphp
<div class="card ws-category-card shadow-sm h-100">
    <a href="{{ route('site.webshop.categories.show', $cat) }}" class="text-decoration-none text-dark h-100">
        @if($image)
            @if($cat->list_image_mode === 'cropped_upload' && (int)$cat->card_width_units > 1)
                <div class="card-img-top text-center bg-light d-flex align-items-center justify-content-center">
                    <picture style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;">
                        <source media="(min-width: 992px)" srcset="{{ $cat->getListImageWideUrl() }}">
                        <img src="{{ $cat->getListImageUrl() }}"
                             alt="{{ $cat->name_plural }}"
                             class="img-fluid"
                             style="object-fit: cover;"
                        >
                    </picture>
                </div>
            @else
                <div class="card-img-top text-center bg-light d-flex align-items-center justify-content-center" style="height: 220px;">
                    <img src="{{ $image }}" alt="{{ $cat->name_plural }}" class="img-fluid" style="max-height: 100%;">
                </div>
            @endif
        @else
            <div class="card-img-top text-center bg-light d-flex align-items-center justify-content-center" style="height: 220px;">
                <i class="fa fa-folder-open fa-4x text-muted"></i>
            </div>
        @endif
        <div class="card-body text-center">
            <h5 class="card-title font-weight-bold mb-2">{{ $cat->name_plural }}</h5>
        </div>
    </a>
</div>
