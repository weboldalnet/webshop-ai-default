<div class="ws-cart-items">
    @forelse($items as $productId => $item)
        <div class="d-flex align-items-center mb-3 pb-3 border-bottom js-cart-item" data-id="{{ $productId }}">
            <div class="mr-3" style="width: 50px;">
                @if($item['image'])
                    <img src="{{ $item['image'] }}" class="img-fluid rounded">
                @else
                    <i class="fa fa-image text-muted"></i>
                @endif
            </div>
            <div class="flex-grow-1">
                <div class="font-weight-bold small text-truncate" style="max-width: 180px;">{{ $item['name'] }}</div>
                <div class="small text-muted">{{ $item['quantity'] }} db × {{ hufFormat($item['price']) }}</div>
            </div>
            <button type="button" class="btn btn-sm btn-link text-danger js-remove-cart-item" data-id="{{ $productId }}">
                <i class="fa fa-trash-can"></i>
            </button>
        </div>
    @empty
        <div class="text-center p-3 text-muted">A kosár jelenleg üres.</div>
    @endforelse
</div>

@if(!empty($items))
    <div class="ws-cart-summary mt-3">
        <div class="d-flex justify-content-between font-weight-bold h5 mb-3">
            <span>Összesen:</span>
            <span>{{ hufFormat($total) }}</span>
        </div>
        <a href="{{ route('site.webshop.checkout.index') }}" class="btn btn-primary btn-block font-weight-bold">
            Rendelés / Ajánlatkérés <i class="fa fa-arrow-right ml-2"></i>
        </a>
    </div>
@endif
