@php
    $image = $cat->og_img ?? $cat->icon ?? ($cat->products()->active()->first()->primary_image ?? null);
@endphp
<div class="card ws-category-card shadow-sm h-100">
    <a href="{{ route('site.webshop.categories.show', $cat) }}" class="text-decoration-none text-dark h-100">
        <div class="card-img-top text-center bg-light d-flex align-items-center justify-content-center" style="height: 220px;">
            @if($image)
                <img src="{{ $image }}" alt="{{ $cat->name_plural }}" class="img-fluid" style="max-height: 100%;">
            @else
                <i class="fa fa-folder-open fa-4x text-muted"></i>
            @endif
        </div>
        <div class="card-body text-center">
            <h5 class="card-title font-weight-bold mb-2">{{ $cat->name_plural }}</h5>
        </div>
    </a>
</div>
