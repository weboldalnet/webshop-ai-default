<div class="modal fade ws-related-products-modal" id="relatedProductsModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white py-2">
                <h5 class="modal-title fw-600"><i class="fa fa-check-circle mr-2"></i> Termék a kosárba került!</h5>
                <button type="button" class="btn btn-sm btn-light shadow-none lh-1" data-dismiss="modal" aria-label="Close">
                    <i class="fa fa-times fs-20"></i>
                </button>
            </div>
            <div class="modal-body p-0">
                <div class="py-2 px-3 bg-light border-bottom">
                    <p class="fw-600 fs-18 lh-12 mb-0">Ezeket a termékeket is ajánljuk Önnek:</p>
                </div>
                <div class="ws-related-products-scroll p-3" style="max-height: 500px; overflow-y: auto;">
                    @foreach($relatedProductsGrouped as $catId => $relProducts)
                        <div class="ws-related-category-group mb-2">
                            <h6 class="ws-related-category-title font-weight-bold text-muted text-uppercase mb-2 border-bottom pb-1">
                                {{ $relProducts->first()->category->name_plural ?? 'Egyéb termékek' }}
                            </h6>
                            <div class="row">
                                @foreach($relProducts as $rel)
                                    <div class="col-md-6 mb-lg-3 mb-2">
                                        <div class="ws-related-product-item d-flex align-items-center p-2 border rounded bg-white shadow-sm h-100">
                                            <div class="ws-related-product-thumb mr-3" style="width: 80px; height: 80px; flex-shrink: 0;">
                                                @if($rel->primary_image_thumb || $rel->primary_image)
                                                    <img src="{{ $rel->primary_image_thumb ?? $rel->primary_image }}" class="img-fluid rounded" style="width: 80px; height: 80px; object-fit: contain;">
                                                @else
                                                    <div class="bg-light d-flex align-items-center justify-content-center h-100 w-100 rounded">
                                                        <i class="fa fa-image text-muted opacity-25"></i>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="ws-related-product-info flex-grow-1 min-w-0 pr-2 lh-12">
                                                <div class="ws-related-product-title fw-600 fs-14 mb-1 text-truncate" title="{{ $rel->name }}">{{ $rel->name }}</div>
                                                @if(($ws['site_product_prices_visible'] ?? 'true') === 'true')
                                                    <div class="ws-related-product-price text-primary font-weight-bold fs-14 mb-2">
                                                        @include('site.webshop.partials.product-price', ['product' => $rel, 'priceSize' => 'small'])
                                                    </div>
                                                @endif
                                                <button type="button" class="btn btn-sm btn-primary ws-related-product-cart js-add-to-cart" data-id="{{ $rel->id }}">
                                                    <i class="fa fa-shopping-cart mr-1"></i> Kosárba
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-outline-secondary fw-600" data-dismiss="modal">Tovább vásárolok</button>
                <a href="{{ route('site.webshop.checkout.index') }}" class="btn btn-success fw-600 px-4">
                    @if(($ws['site_checkout_mode'] ?? 'order') === 'quote')
                        Ajánlatkérés küldése
                    @else
                        Rendelés leadása
                    @endif
                    <i class="fa fa-chevron-right ml-2"></i>
                </a>
            </div>
        </div>
    </div>
</div>
