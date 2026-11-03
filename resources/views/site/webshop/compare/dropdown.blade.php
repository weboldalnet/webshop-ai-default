<div class="ws-compare-items">
    @forelse($items as $productId => $item)
        <div class="d-flex align-items-center mb-2 pb-2 border-bottom js-compare-item" data-id="{{ $productId }}">
            <div class="flex-grow-1">
                <div class="font-weight-bold fs-16 text-truncate" style="max-width: 200px;">{{ $item['name'] }}</div>
                <div class="badge badge-info small">{{ $item['category_name'] }}</div>
            </div>
            <button type="button" class="btn btn-sm btn-link text-danger js-remove-compare-item" data-id="{{ $productId }}">
                <i class="fa fa-times"></i>
            </button>
        </div>
    @empty
        <div class="text-center p-3 text-muted">{{ __('webshop::catalog.compare.empty') }}</div>
    @endforelse
</div>

@if(count($items) >= 2)
    <div class="mt-2">
        <a href="{{ WsUrl::route('site.webshop.compare.index') }}" class="btn btn-info btn-block fw-600 fs-16">
            {{ __('webshop::product.actions.compare') }} <i class="fa fa-scale-balanced ml-2"></i>
        </a>
    </div>
@elseif(count($items) == 1)
    <div class="alert alert-warning small mt-2">{{ __('webshop::catalog.compare.min_items') }}</div>
@endif
