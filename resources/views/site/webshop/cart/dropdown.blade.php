<div class="ws-cart-items">
    @forelse($items as $productId => $item)
        <div class="d-flex align-items-center mb-2 pb-2 border-bottom js-cart-item" data-id="{{ $productId }}">
            <div class="mr-2" style="width: 50px;">
                @if($item['image_thumb'])
                    <img src="{{ $item['image_thumb'] }}" class="img-fluid rounded">
                @else
                    <i class="fa fa-image text-muted"></i>
                @endif
            </div>
            <div class="flex-grow-1">
                <div class="font-weight-bold fs-14 text-truncate lh-12" style="max-width: 180px;">
                    {{ $item['name'] }}
                    @if($item['sec_name'])
                        @if(($ws['site_product_prices_visible'] ?? 'true') === 'false') <br> @endif
                        <span class="font-weight-light fs-13">{{ $item['sec_name'] }}</span>
                    @endif
                </div>
                <div class="small text-muted">
                    {{ trans_choice('webshop::common.quantity_pcs', $item['quantity'], ['count' => $item['quantity']]) }} @if(($ws['site_product_prices_visible'] ?? 'true') === 'true')× {{ WsFormat::money($item['price']) }} @endif
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-link text-danger js-remove-cart-item" data-id="{{ $productId }}">
                <i class="fa fa-trash-can"></i>
            </button>
        </div>
    @empty
        <div class="text-center p-3 text-muted">{{ __('webshop::cart.dropdown.empty') }}</div>
    @endforelse
</div>

@if(!empty($items))
    <div class="ws-cart-summary mt-3">
        @if(($ws['site_product_prices_visible'] ?? 'true') === 'true')
            <div class="d-flex justify-content-between font-weight-bold h5 mb-2">
                <span>{{ __('webshop::cart.dropdown.total') }}</span>
                <span>{{ WsFormat::money($total) }}</span>
            </div>
        @endif
        <a href="{{ WsUrl::route('site.webshop.checkout.index') }}" class="btn btn-primary btn-block fw-600 fs-16">
            <i class="fa {{ ($ws['site_checkout_mode'] ?? 'order') == 'quote' ? 'fa-file-invoice' : 'fa-cart-check' }} mr-2"></i>
            {{ ($ws['site_checkout_mode'] ?? 'order') == 'quote' ? __('webshop::cart.dropdown.quote') : __('webshop::cart.dropdown.checkout') }} <i class="fa fa-arrow-right ml-2"></i>
        </a>
    </div>
@endif
